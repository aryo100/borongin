<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\ShippingRate;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class Checkout
{
    public const SESSION_KEY = 'store.checkout_key';

    public function __construct(private ShoppingCart $shoppingCart) {}

    public function place(array $cart, array $data, ?User $user): Order
    {
        $key = $data['idempotency_key'];
        $existing = $this->existingOrder($key, $user);

        if ($existing) {
            return $existing;
        }

        if ($cart === []) {
            throw ValidationException::withMessages(['cart' => 'Tambahkan produk sebelum membuat pesanan.']);
        }

        try {
            return DB::transaction(function () use ($cart, $data, $user, $key): Order {
                $productIds = Variant::query()->whereKey(array_keys($cart))->pluck('product_id')->unique();
                $products = Product::query()->whereKey($productIds)->orderBy('id')->sharedLock()->get()->keyBy('id');
                $variants = Variant::query()->whereKey(array_keys($cart))->orderBy('id')->lockForUpdate()->get();

                // A concurrent request may have used this key while this request waited for stock.
                $existing = $this->existingOrder($key, $user);
                if ($existing) {
                    return $existing;
                }

                if ($variants->count() !== count($cart)) {
                    throw ValidationException::withMessages(['cart' => 'Sebagian produk sudah tidak tersedia. Periksa kembali keranjang Anda.']);
                }

                $rate = ShippingRate::query()->where('active', true)->sharedLock()->find($data['shipping_rate_id']);
                if (! $rate) {
                    throw ValidationException::withMessages(['shipping_rate_id' => 'Pilihan pengiriman sudah tidak tersedia.']);
                }

                $address = $this->address($data, $rate);
                $isMember = $user?->membership()->sharedLock()->first()?->status === 'approved';
                $items = [];
                $subtotal = 0;
                $eta = 0;

                foreach ($variants as $variant) {
                    $variant->setRelation('product', $products->get($variant->product_id));
                    $quantity = $cart[$variant->id];
                    $this->shoppingCart->validatePurchase($variant, $quantity, $isMember, 'cart');

                    $lead = $variant->preorder ? $variant->lead_days : 0;
                    $subtotal += $variant->price * $quantity;
                    $eta = max($eta, $lead);
                    $items[] = [
                        'variant_id' => $variant->id,
                        'product_name' => $variant->product->name,
                        'variant_name' => $variant->name,
                        'sku' => $variant->sku,
                        'unit_price' => $variant->price,
                        'quantity' => $quantity,
                        'lead_days' => $lead,
                    ];
                }

                $order = Order::create([
                    'public_id' => Str::ulid()->toString(),
                    'user_id' => $user?->id,
                    'name' => $data['name'],
                    'email' => mb_strtolower($data['email']),
                    'phone' => $data['phone'],
                    'address' => $address,
                    'shipping_name' => $rate->name,
                    'shipping_fee' => $rate->price,
                    'subtotal' => $subtotal,
                    'total' => $subtotal + $rate->price,
                    'eta_days' => $eta,
                    'status' => 'awaiting_payment',
                    'payment_due_at' => now()->addDays(max(1, config('borongin.order_payment_days'))),
                    'idempotency_key' => $key,
                ]);

                $order->items()->createMany($items);
                foreach ($variants as $variant) {
                    $variant->increment('reserved', $cart[$variant->id]);
                }

                return $order;
            }, attempts: 5);
        } catch (UniqueConstraintViolationException $exception) {
            // The unique key also covers concurrent submissions involving disjoint products.
            return $this->existingOrder($key, $user) ?? throw $exception;
        }
    }

    private function existingOrder(string $key, ?User $user): ?Order
    {
        $order = Order::query()->where('idempotency_key', $key)->first();

        if ($order && $order->user_id !== $user?->id) {
            throw ValidationException::withMessages(['idempotency_key' => 'Segarkan halaman checkout sebelum melanjutkan.']);
        }

        return $order;
    }

    private function address(array $data, ShippingRate $rate): ?array
    {
        if ($rate->pickup) {
            return null;
        }

        $address = Validator::make($data, [
            'address_line' => ['required', 'string', 'max:250'],
            'city' => ['required', 'string', 'max:120'],
            'province' => ['required', 'string', 'max:120'],
            'postal_code' => ['required', 'digits:5'],
        ])->validate();

        if ($this->normalizeRegion($address['province']) !== $this->normalizeRegion((string) $rate->region)) {
            throw ValidationException::withMessages(['shipping_rate_id' => 'Pilih pengiriman yang sesuai dengan provinsi tujuan.']);
        }

        $address['province'] = $rate->region;

        return $address;
    }

    private function normalizeRegion(string $region): string
    {
        return Str::of($region)->squish()->lower()->toString();
    }
}
