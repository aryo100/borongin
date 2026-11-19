<?php

namespace App\Services;

use App\Mail\OrderAccess;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Throwable;

class GuestOrderAccess
{
    private const SESSION_KEY = 'store.guest_orders';

    public function grant(Request $request, Order $order): void
    {
        $active = array_filter($request->session()->get(self::SESSION_KEY, []), fn ($expires) => $expires > now()->timestamp);
        $active[$order->public_id] = now()->addWeek()->timestamp;
        $request->session()->put(self::SESSION_KEY, $active);
    }

    public function granted(Request $request, Order $order): bool
    {
        return $request->hasSession()
            && ($request->session()->get(self::SESSION_KEY, [])[$order->public_id] ?? 0) > now()->timestamp;
    }

    public function send(Order $order): void
    {
        $path = URL::temporarySignedRoute('store.orders.access', now()->addDay(), ['order' => $order->public_id], absolute: false);
        $url = rtrim(config('app.url'), '/').$path;

        try {
            Mail::to($order->email)->queue(new OrderAccess($order->public_id, $url));
        } catch (Throwable $exception) {
            // The order has already committed, and its access link can be requested again.
            report($exception);
        }
    }
}
