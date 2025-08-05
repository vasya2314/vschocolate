<?php

namespace App\Actions\Order;

use App\Models\Order;

class DeleteOrderAction
{
    public function handle(Order $order): ?bool
    {
        return $order->delete();
    }
}
