<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Membership;
use App\Models\Order;
use App\Models\Ticket;
use App\Models\Variant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $counts = [
            'sales' => (int) Order::query()->whereIn('status', ['processing', 'shipped', 'completed'])->sum('subtotal'),
            'payments' => Order::query()->where('status', 'payment_review')->count(),
            'processing' => Order::query()->where('status', 'processing')->count(),
            'ordersToday' => Order::query()->where('created_at', '>=', now('Asia/Jakarta')->startOfDay()->utc())->count(),
            'stock' => Variant::query()->where('active', true)
                ->whereHas('product', fn ($products) => $products->where('active', true))
                ->where('preorder', false)->whereRaw('stock <= reserved + 5')->count(),
            'memberships' => Membership::query()->where('status', 'pending')->count(),
            'tickets' => Ticket::query()->where('status', 'open')->count(),
        ];

        $orders = Order::query()->latest()->limit(7)->get();

        return view('admin.dashboard', compact('counts', 'orders'));
    }
}
