<?php

namespace App\Services;

use App\Models\User;
use App\Models\Variant;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class ShoppingCart
{
    public const SESSION_KEY = 'store.cart';

    public const MAX_QUANTITY = 999;

    public function lines(array $cart): Collection
    {
        $variants = Variant::query()->with(['product.images'])->findMany(array_keys($cart))->keyBy('id');

        return collect($cart)->filter(fn ($quantity, $id) => $variants->has($id))
            ->map(function (int $quantity, int $id) use ($variants): array {
                $variant = $variants->get($id);

                return [
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'subtotal' => $variant->price * $quantity,
                ];
            })->values();
    }

    public function isMember(?User $user): bool
    {
        return $user?->membership()->where('status', 'approved')->exists() ?? false;
    }

    public function validatePurchase(Variant $variant, int $quantity, bool $isMember, string $field): void
    {
        if (! $variant->active || ! $variant->product?->active) {
            throw ValidationException::withMessages([$field => 'Pilihan produk ini sudah tidak tersedia.']);
        }

        if ($variant->product->members_only && ! $isMember) {
            throw ValidationException::withMessages([$field => 'Produk ini khusus anggota sekolah yang telah disetujui.']);
        }

        if ($quantity < 1 || $quantity > self::MAX_QUANTITY || $quantity > $variant->available) {
            throw ValidationException::withMessages([$field => "Jumlah tersedia untuk {$variant->product->name} ({$variant->name}) telah berubah. Silakan sesuaikan jumlahnya."]);
        }
    }
}
