<?php

namespace App\Http\Controllers\Store;

use App\Models\Variant;
use App\Services\Checkout;
use App\Services\ShoppingCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController
{
    public function __construct(private ShoppingCart $shoppingCart) {}

    public function show(Request $request): View
    {
        $cart = $request->session()->get(ShoppingCart::SESSION_KEY, []);
        $lines = $this->shoppingCart->lines($cart);

        if ($lines->count() !== count($cart)) {
            $this->save($request, $lines->mapWithKeys(fn (array $line) => [$line['variant']->id => $line['quantity']])->all());
            $request->session()->now('status', 'Produk yang sudah tidak tersedia telah dihapus dari keranjang.');
        }

        return view('store.cart', ['lines' => $lines, 'subtotal' => $lines->sum('subtotal')]);
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer'],
            'quantity' => ['required', 'integer', 'min:1', 'max:'.ShoppingCart::MAX_QUANTITY],
        ]);
        $variant = Variant::find($data['variant_id']);
        if (! $variant) {
            throw ValidationException::withMessages(['variant_id' => 'Pilihan produk ini sudah tidak tersedia.']);
        }

        $cart = $request->session()->get(ShoppingCart::SESSION_KEY, []);
        $this->setQuantity($request, $cart, $variant, ($cart[$variant->id] ?? 0) + $data['quantity']);

        return redirect()->route('store.cart')->with('status', 'Produk ditambahkan ke keranjang.');
    }

    public function update(Request $request, Variant $variant): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:'.ShoppingCart::MAX_QUANTITY]]);
        $cart = $request->session()->get(ShoppingCart::SESSION_KEY, []);

        if (! array_key_exists($variant->id, $cart)) {
            throw ValidationException::withMessages(['quantity' => 'Produk ini belum ada di keranjang.']);
        }

        $this->setQuantity($request, $cart, $variant, (int) $data['quantity']);

        return redirect()->route('store.cart')->with('status', 'Keranjang diperbarui.');
    }

    public function remove(Request $request, int $variant): RedirectResponse
    {
        $cart = $request->session()->get(ShoppingCart::SESSION_KEY, []);
        unset($cart[$variant]);
        $this->save($request, $cart);

        return redirect()->route('store.cart')->with('status', 'Produk dihapus dari keranjang.');
    }

    private function setQuantity(Request $request, array $cart, Variant $variant, int $quantity): void
    {
        $variant->load('product');
        $this->shoppingCart->validatePurchase($variant, $quantity, $this->shoppingCart->isMember($request->user()), 'quantity');
        $cart[$variant->id] = $quantity;
        $this->save($request, $cart);
    }

    private function save(Request $request, array $cart): void
    {
        $request->session()->put(ShoppingCart::SESSION_KEY, $cart);
        $request->session()->forget(Checkout::SESSION_KEY);
    }
}
