<?php

namespace App\Actions\Order;

use App\Models\Order;

class RestoreOrderAction
{
    public function handle(Order $order): ?bool
    {
        return $order->restore();
    }
}
