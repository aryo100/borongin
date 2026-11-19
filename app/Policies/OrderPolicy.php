<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;
use App\Services\GuestOrderAccess;

class OrderPolicy
{
    public function __construct(private GuestOrderAccess $access) {}

    public function view(?User $user, Order $order): bool
    {
        if ($order->user_id !== null) {
            return $user?->id === $order->user_id;
        }

        return $this->access->granted(request(), $order);
    }
}
