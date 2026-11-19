<?php

namespace App\Http\Controllers\Store;

use App\Models\Order;
use App\Models\Setting;
use App\Models\ShippingRate;
use App\Services\Checkout;
use App\Services\GuestOrderAccess;
use App\Services\ShoppingCart;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CheckoutController
{
    public function show(Request $request, ShoppingCart $cart): Response|RedirectResponse
    {
        $quantities = $request->session()->get(ShoppingCart::SESSION_KEY, []);
        $lines = $cart->lines($quantities);
        if ($lines->isEmpty() || $lines->count() !== count($quantities)) {
            return redirect()->route('store.cart');
        }

        $key = $request->session()->get(Checkout::SESSION_KEY);
        if (! $key || Order::query()->where('idempotency_key', $key)->exists()) {
            $key = Str::uuid()->toString();
            $request->session()->put(Checkout::SESSION_KEY, $key);
        }

        return response()->view('store.checkout', [
            'lines' => $lines,
            'subtotal' => $lines->sum('subtotal'),
            'rates' => ShippingRate::query()->where('active', true)->orderBy('pickup')->orderBy('region')->orderBy('name')->get(),
            'idempotencyKey' => $key,
            'settings' => Setting::query()->pluck('value', 'key'),
        ], headers: ['Cache-Control' => 'private, no-store']);
    }

    public function place(Request $request, Checkout $checkout, GuestOrderAccess $access): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['required', 'string', 'max:30', 'regex:/^\+?[0-9][0-9(). \-]{6,28}[0-9]$/'],
            'shipping_rate_id' => ['required', 'integer', 'min:1'],
            'idempotency_key' => ['required', 'uuid'],
            'address_line' => ['nullable', 'string', 'max:250'],
            'city' => ['nullable', 'string', 'max:120'],
            'province' => ['nullable', 'string', 'max:120'],
            'postal_code' => ['nullable', 'string', 'max:10'],
        ]);

        if ($request->session()->get(Checkout::SESSION_KEY) !== $data['idempotency_key']) {
            throw ValidationException::withMessages(['idempotency_key' => 'Segarkan halaman checkout sebelum melanjutkan.']);
        }

        $order = $checkout->place($request->session()->get(ShoppingCart::SESSION_KEY, []), $data, $request->user());
        $request->session()->forget(ShoppingCart::SESSION_KEY);

        if ($order->user_id === null) {
            $access->grant($request, $order);
            if ($order->wasRecentlyCreated) {
                $access->send($order);
            }
        }

        return redirect()->route('store.orders.show', ['order' => $order->public_id])
            ->with('status', 'Pesanan berhasil dibuat. Lanjutkan dengan transfer dan unggah bukti pembayaran.');
    }
}
