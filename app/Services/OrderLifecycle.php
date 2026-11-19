<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use LogicException;

class OrderLifecycle
{
    public function submitPayment(Order $order, string $path): void
    {
        DB::transaction(function () use ($order, $path): void {
            $locked = $this->lockOrder($order->id);

            if ($locked->status !== 'awaiting_payment' || $locked->payment_due_at->lte(now())) {
                throw ValidationException::withMessages(['proof' => 'Pesanan ini tidak sedang menerima bukti pembayaran.']);
            }

            $locked->payments()->create(['path' => $path, 'mime' => 'image/jpeg', 'status' => 'pending']);
            $locked->update(['status' => 'payment_review']);
        }, attempts: 5);
    }

    public function approvePayment(Payment $payment, User $reviewer, ?string $note = null): void
    {
        $this->reviewPayment($payment, $reviewer, 'approved', $note);
    }

    public function rejectPayment(Payment $payment, User $reviewer, ?string $note = null): void
    {
        $this->reviewPayment($payment, $reviewer, 'rejected', $note);
    }

    public function cancel(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $locked = $this->lockOrder($order->id);

            if ($locked->status === 'cancelled') {
                return;
            }

            $this->requireStatus($locked, ['awaiting_payment', 'payment_review']);
            $this->settleStock($locked, consume: false);
            $locked->payments()->where('status', 'pending')->update([
                'status' => 'rejected',
                'note' => 'Pesanan dibatalkan.',
                'reviewed_at' => now(),
            ]);
            $locked->update(['status' => 'cancelled']);
        }, attempts: 5);
    }

    public function ship(Order $order, string $trackingNumber): void
    {
        DB::transaction(function () use ($order, $trackingNumber): void {
            $locked = $this->lockOrder($order->id);

            if ($locked->status === 'shipped' && $locked->tracking_number === $trackingNumber) {
                return;
            }

            $this->requireStatus($locked, ['processing']);
            $locked->update(['status' => 'shipped', 'tracking_number' => $trackingNumber]);
        }, attempts: 5);
    }

    public function complete(Order $order): void
    {
        DB::transaction(function () use ($order): void {
            $locked = $this->lockOrder($order->id);

            if ($locked->status === 'completed') {
                return;
            }

            // Pickup orders have no delivery address or courier tracking number.
            $this->requireStatus($locked, $locked->address === null ? ['processing', 'shipped'] : ['shipped']);
            $locked->update(['status' => 'completed']);
        }, attempts: 5);
    }

    public function expireDue(): int
    {
        $expired = 0;

        Order::query()->where('status', 'awaiting_payment')->where('payment_due_at', '<=', now())
            ->select('id')->chunkById(100, function ($orders) use (&$expired): void {
                foreach ($orders as $order) {
                    $expired += (int) $this->expire($order->id);
                }
            });

        return $expired;
    }

    private function expire(int $id): bool
    {
        return DB::transaction(function () use ($id): bool {
            $order = $this->lockOrder($id);

            if ($order->status !== 'awaiting_payment' || $order->payment_due_at->isFuture()) {
                return false;
            }

            $this->settleStock($order, consume: false);
            $order->update(['status' => 'expired']);

            return true;
        }, attempts: 5);
    }

    private function reviewPayment(Payment $payment, User $reviewer, string $status, ?string $note): void
    {
        abort_unless($reviewer->role === 'admin', 403);

        DB::transaction(function () use ($payment, $reviewer, $status, $note): void {
            $order = $this->lockOrder($payment->order_id);
            $locked = $order->payments()->lockForUpdate()->findOrFail($payment->id);

            if ($locked->status === $status) {
                return;
            }

            $this->requireStatus($order, ['payment_review']);
            if ($locked->status !== 'pending') {
                throw ValidationException::withMessages(['status' => 'Bukti pembayaran ini sudah ditinjau.']);
            }

            if ($status === 'approved') {
                $this->settleStock($order, consume: true);
                $order->update(['status' => 'processing']);
            } else {
                $order->update(['status' => 'awaiting_payment', 'payment_due_at' => now()->addDay()]);
            }

            $locked->update([
                'status' => $status,
                'note' => $note,
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);
        }, attempts: 5);
    }

    private function lockOrder(int $id): Order
    {
        return Order::query()->lockForUpdate()->findOrFail($id);
    }

    private function requireStatus(Order $order, array $allowed): void
    {
        if (! in_array($order->status, $allowed, true)) {
            throw ValidationException::withMessages(['status' => 'Tindakan ini tidak tersedia untuk status pesanan saat ini.']);
        }
    }

    private function settleStock(Order $order, bool $consume): void
    {
        $quantities = $order->items()->get(['variant_id', 'quantity'])
            ->groupBy('variant_id')->map(fn ($items) => $items->sum('quantity'));
        $variants = Variant::query()->whereKey($quantities->keys())->orderBy('id')->lockForUpdate()->get();

        if ($variants->count() !== $quantities->count()) {
            throw new LogicException('A reserved order no longer has all of its variants.');
        }

        foreach ($variants as $variant) {
            $quantity = $quantities->get($variant->id);

            if ($quantity > $variant->reserved || $quantity > $variant->stock) {
                throw new LogicException('The stock reservation is inconsistent.');
            }

            $variant->update([
                'reserved' => $variant->reserved - $quantity,
                'stock' => $consume ? $variant->stock - $quantity : $variant->stock,
            ]);
        }
    }
}
