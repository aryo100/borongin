<?php

namespace App\Http\Controllers\Store;

use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Services\ShoppingCart;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CatalogController
{
    public function home(): View
    {
        return view('store.home', [
            'categories' => Category::query()->orderBy('name')->get(),
            'featured' => $this->products()->where('featured', true)->latest()->limit(8)->get(),
            'settings' => Setting::query()->pluck('value', 'key'),
        ]);
    }

    public function index(Request $request): View
    {
        $query = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:160'],
        ]);
        $search = $query['q'] ?? '';
        $selectedCategory = $query['category'] ?? null;

        $products = $this->products()
            ->when($selectedCategory, fn (Builder $products) => $products->whereRelation('category', 'slug', $selectedCategory))
            ->when($search !== '', fn (Builder $products) => $products->where(function (Builder $matches) use ($search): void {
                $matches->whereLike('name', '%'.$search.'%')->orWhereLike('description', '%'.$search.'%');
            }))
            ->orderBy('name')->orderBy('id')->paginate(12)->withQueryString();

        return view('store.catalog', [
            'products' => $products,
            'categories' => Category::query()->orderBy('name')->get(),
            'selectedCategory' => $selectedCategory,
            'search' => $search,
        ]);
    }

    public function show(Request $request, Product $product, ShoppingCart $cart): View
    {
        abort_unless($product->active, 404);

        return view('store.product', [
            'product' => $product->load(['category', 'images', 'variants' => fn ($variants) => $variants->where('active', true)->orderBy('id')]),
            'isMember' => $cart->isMember($request->user()),
        ]);
    }

    public function faq(): View
    {
        return view('store.faq', ['settings' => Setting::query()->pluck('value', 'key')]);
    }

    public function contact(): View
    {
        return view('store.contact', ['settings' => Setting::query()->pluck('value', 'key')]);
    }

    private function products(): Builder
    {
        return Product::query()->where('active', true)
            ->with(['category', 'images', 'variants' => fn ($variants) => $variants->where('active', true)->orderBy('id')]);
    }
}
