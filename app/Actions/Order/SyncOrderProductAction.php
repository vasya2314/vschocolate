<?php

namespace App\Actions\Order;

use App\Models\Order;

class SyncOrderProductAction
{
    public function handle(Order $order, array $productIds): array
    {
        return $order->products()->sync($productIds);
    }
}
