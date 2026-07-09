<?php

use App\Models\Order;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('order.{orderId}', function ($user, $orderId) {
    $order = Order::find($orderId);
    if (! $order) {
        return false;
    }

    return $user->id === $order->buyer_id || $user->id === $order->seller_id;
});
