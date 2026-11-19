<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class CategoryController extends Controller
{
    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->value();

        $categories = Category::query()->withCount('products')
            ->when($query !== '', fn ($builder) => $builder->where('name', 'like', "%{$query}%"))
            ->orderBy('name')->paginate(20)->withQueryString();

        return view('admin.categories.index', compact('categories', 'query'));
    }

    public function create(): View
    {
        return view('admin.categories.form', ['category' => new Category]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->save($request, new Category);

        return to_route('admin.categories.index')->with('status', 'Kategori dibuat.');
    }

    public function edit(Category $category): View
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category): RedirectResponse
    {
        $this->save($request, $category);

        return to_route('admin.categories.index')->with('status', 'Kategori diperbarui.');
    }

    public function destroy(Category $category): RedirectResponse
    {
        $path = DB::transaction(function () use ($category): ?string {
            $locked = Category::query()->lockForUpdate()->findOrFail($category->id);
            if ($locked->products()->exists()) {
                throw ValidationException::withMessages(['category' => 'Pindahkan semua produk dari kategori ini sebelum menghapusnya.']);
            }

            $locked->delete();

            return $locked->image_path;
        }, 3);

        if ($path && str_starts_with($path, 'categories/')) {
            Storage::disk('public')->delete($path);
        }

        return to_route('admin.categories.index')->with('status', 'Kategori dihapus.');
    }

    private function save(Request $request, Category $category): void
    {
        $request->merge(['slug' => Str::slug($request->string('slug')->trim()->value() ?: $request->string('name')->value())]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:150', Rule::unique('categories')->ignore($category)],
            'description' => ['nullable', 'string', 'max:3000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=6000,max_height=6000'],
            'remove_image' => ['sometimes', 'boolean'],
        ]);

        $newPath = $request->file('image')?->store('categories', 'public');
        abort_if($newPath === false, 503, 'Gambar tidak dapat disimpan.');

        try {
            $unusedPath = DB::transaction(function () use ($category, $data, $request, $newPath): ?string {
                $locked = $category->exists ? Category::query()->lockForUpdate()->findOrFail($category->id) : $category;
                $previousPath = $locked->image_path;
                $locked->fill([
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => $data['description'] ?? null,
                    'image_path' => $newPath ?? ($request->boolean('remove_image') ? null : $previousPath),
                ])->save();

                return $previousPath !== $locked->image_path ? $previousPath : null;
            }, 3);
        } catch (Throwable $exception) {
            if ($newPath) {
                Storage::disk('public')->delete($newPath);
            }

            throw $exception;
        }

        if ($unusedPath && str_starts_with($unusedPath, 'categories/')) {
            Storage::disk('public')->delete($unusedPath);
        }
    }
}
