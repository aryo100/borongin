<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->value();
        $categoryId = $request->integer('category');
        $visibility = $request->string('visibility')->value();
        $stock = $request->string('stock')->value();
        $products = Product::query()
            ->with(['category', 'images'])
            ->withCount('variants')
            ->when($query !== '', fn ($builder) => $builder->where(function ($builder) use ($query): void {
                $builder->where('name', 'like', "%{$query}%")->orWhereHas('variants', fn ($variants) => $variants->where('sku', 'like', "%{$query}%"));
            }))
            ->when($categoryId > 0, fn ($builder) => $builder->where('category_id', $categoryId))
            ->when(in_array($visibility, ['active', 'inactive'], true), fn ($builder) => $builder->where('active', $visibility === 'active'))
            ->when($stock === 'low', fn ($builder) => $builder->where('active', true)
                ->whereHas('variants', fn ($variants) => $variants->where('active', true)
                    ->where('preorder', false)->whereRaw('stock <= reserved + 5')))
            ->latest()->paginate(20)->withQueryString();
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.index', compact('products', 'categories', 'query', 'categoryId', 'visibility', 'stock'));
    }

    public function create(): View
    {
        return $this->form(new Product(['active' => true]));
    }

    public function store(Request $request): RedirectResponse
    {
        $product = new Product;
        $this->save($request, $product);

        return to_route('admin.products.edit', $product)->with('status', 'Produk dibuat. Tambahkan varian agar pelanggan dapat membelinya.');
    }

    public function edit(Product $product): View
    {
        $product->load(['variants', 'images']);

        return $this->form($product);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $this->save($request, $product);

        return to_route('admin.products.edit', $product)->with('status', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $imagePaths = DB::transaction(function () use ($product) {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $variantIds = $locked->variants()->orderBy('id')->lockForUpdate()->pluck('id');

            if (OrderItem::query()->whereIn('variant_id', $variantIds)->exists()) {
                $locked->update(['active' => false]);

                return null;
            }

            $paths = $locked->images()->pluck('path')->all();
            $locked->delete();

            return $paths;
        }, 3);

        if ($imagePaths === null) {
            return to_route('admin.products.index')->with('status', 'Produk dinonaktifkan. Riwayat pesanannya tetap tersimpan.');
        }

        Storage::disk('public')->delete(array_filter($imagePaths, fn (string $path): bool => str_starts_with($path, 'products/')));

        return to_route('admin.products.index')->with('status', 'Produk dihapus.');
    }

    public function removeImage(Product $product, int $image): RedirectResponse
    {
        $photo = $product->images()->findOrFail($image);
        $photo->delete();
        if (str_starts_with($photo->path, 'products/')) {
            Storage::disk('public')->delete($photo->path);
        }

        return to_route('admin.products.edit', $product)->with('status', 'Foto produk dihapus.');
    }

    private function form(Product $product): View
    {
        $categories = Category::query()->orderBy('name')->get();

        return view('admin.products.form', compact('product', 'categories'));
    }

    private function save(Request $request, Product $product): void
    {
        $request->merge(['slug' => Str::slug($request->string('slug')->trim()->value() ?: $request->string('name')->value())]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', Rule::unique('products')->ignore($product)],
            'category_id' => ['required', 'integer', Rule::exists('categories', 'id')],
            'description' => ['nullable', 'string', 'max:10000'],
            'active' => ['sometimes', 'boolean'],
            'members_only' => ['sometimes', 'boolean'],
            'featured' => ['sometimes', 'boolean'],
            'photos' => ['sometimes', 'array', 'max:8'],
            'photos.*' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
        ]);

        if (collect($request->file('photos', []))->sum(fn ($photo) => $photo->getSize()) > 7 * 1024 * 1024) {
            throw ValidationException::withMessages(['photos' => 'Ukuran seluruh foto dalam satu unggahan tidak boleh melebihi 7 MB.']);
        }

        $paths = [];
        try {
            foreach ($request->file('photos', []) as $photo) {
                $path = $photo->store('products', 'public');
                abort_if($path === false, 503, 'Foto tidak dapat disimpan.');
                $paths[] = $path;
            }

            DB::transaction(function () use ($product, $data, $request, $paths): void {
                if (! Category::query()->sharedLock()->find($data['category_id'])) {
                    throw ValidationException::withMessages(['category_id' => 'Kategori ini sudah tidak tersedia.']);
                }

                $locked = $product->exists ? Product::query()->lockForUpdate()->findOrFail($product->id) : $product;
                $locked->fill([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'category_id' => $data['category_id'],
                    'description' => $data['description'] ?? null,
                    'active' => $request->boolean('active'),
                    'members_only' => $request->boolean('members_only'),
                    'featured' => $request->boolean('featured'),
                ])->save();

                $position = (int) $locked->images()->max('position');
                foreach ($paths as $path) {
                    $locked->images()->create(['path' => $path, 'alt' => $locked->name, 'position' => ++$position]);
                }
            });
        } catch (Throwable $exception) {
            Storage::disk('public')->delete($paths);

            throw $exception;
        }
    }
}
