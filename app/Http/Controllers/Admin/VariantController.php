<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Variant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VariantController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, new Variant);

        DB::transaction(function () use ($product, $data): void {
            Product::query()->lockForUpdate()->findOrFail($product->id)->variants()->create($data);
        }, 3);

        return to_route('admin.products.edit', $product)->with('status', 'Varian ditambahkan.');
    }

    public function update(Request $request, Product $product, int $variant): RedirectResponse
    {
        $item = $product->variants()->findOrFail($variant);
        $data = $this->validated($request, $item);

        DB::transaction(function () use ($product, $variant, $data): void {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $item = $locked->variants()->lockForUpdate()->findOrFail($variant);

            if ($data['stock'] < $item->reserved) {
                throw ValidationException::withMessages(['stock' => 'Stok tidak boleh lebih kecil dari jumlah yang sedang dipesan.']);
            }

            $item->update($data);
        }, 3);

        return to_route('admin.products.edit', $product)->with('status', 'Varian diperbarui.');
    }

    public function destroy(Product $product, int $variant): RedirectResponse
    {
        $archived = DB::transaction(function () use ($product, $variant): bool {
            $locked = Product::query()->lockForUpdate()->findOrFail($product->id);
            $item = $locked->variants()->lockForUpdate()->findOrFail($variant);

            if (OrderItem::query()->where('variant_id', $item->id)->exists()) {
                $item->update(['active' => false]);

                return true;
            }

            $item->delete();

            return false;
        }, 3);

        return to_route('admin.products.edit', $product)->with('status', $archived ? 'Varian dinonaktifkan. Riwayat pesanannya tetap tersimpan.' : 'Varian dihapus.');
    }

    private function validated(Request $request, Variant $variant): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'sku' => ['required', 'string', 'alpha_dash:ascii', 'max:80', Rule::unique('variants')->ignore($variant)],
            'price' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'stock' => ['required', 'integer', 'min:0', 'max:1000000'],
            'lead_days' => ['required', 'integer', 'min:0', 'max:365'],
            'preorder' => ['sometimes', 'boolean'],
            'active' => ['sometimes', 'boolean'],
        ]);

        $data['preorder'] = $request->boolean('preorder');
        $data['active'] = $request->boolean('active');

        return $data;
    }
}
