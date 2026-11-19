<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderLifecycle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const STATUSES = [
        'awaiting_payment', 'payment_review', 'processing', 'shipped', 'completed', 'cancelled', 'expired',
    ];

    public function index(Request $request): View
    {
        $query = $request->string('q')->trim()->value();
        $status = $request->string('status')->value();
        $date = $request->string('date')->value();
        $statuses = self::STATUSES;

        $orders = Order::query()
            ->when($query !== '', fn ($builder) => $builder->where(function ($builder) use ($query): void {
                $builder->where('public_id', 'like', "%{$query}%")
                    ->orWhere('name', 'like', "%{$query}%")
                    ->orWhere('email', 'like', "%{$query}%")
                    ->orWhere('tracking_number', 'like', "%{$query}%");
            }))
            ->when(in_array($status, $statuses, true), fn ($builder) => $builder->where('status', $status))
            ->when($date === 'today', fn ($builder) => $builder->where('created_at', '>=', now('Asia/Jakarta')->startOfDay()->utc()))
            ->latest()->paginate(20)->withQueryString();

        return view('admin.orders.index', compact('orders', 'query', 'status', 'statuses', 'date'));
    }

    public function show(Order $order): View
    {
        $order->load(['items', 'payments.reviewer', 'tickets']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order, OrderLifecycle $lifecycle): RedirectResponse
    {
        $data = $request->validate([
            'action' => ['required', Rule::in(['ship', 'complete', 'cancel'])],
            'tracking_number' => ['nullable', 'string', 'max:100', 'required_if:action,ship'],
        ]);

        if ($data['action'] === 'ship' && $order->address === null) {
            return back()->withErrors(['action' => 'Pesanan ambil sendiri tidak memerlukan pengiriman.']);
        }

        match ($data['action']) {
            'ship' => $lifecycle->ship($order, $data['tracking_number']),
            'complete' => $lifecycle->complete($order),
            'cancel' => $lifecycle->cancel($order),
        };

        return to_route('admin.orders.show', $order)->with('status', 'Status pesanan diperbarui.');
    }
}
