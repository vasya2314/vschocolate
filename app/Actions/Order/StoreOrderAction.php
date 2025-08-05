<?php

namespace App\Actions\Order;

use App\Exceptions\BusinessException;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class StoreOrderAction
{
    public function __construct(
        protected SyncOrderProductAction $syncOrderProductAction,
    ) {}

    public function handle(array $fields): Order
    {
        return DB::transaction(function () use ($fields) {
            $data = [
                'date_issue' => Carbon::parse($fields['date_issue'])->format('Y-m-d H:i:s'),
                'client_id' => $fields['client_id'],
                'description' => $fields['description'],
                'amount_total' => $fields['amount_total'],
                'amount_payed' => $fields['amount_total'],
                'status' => $fields['status'],
            ];

            $productIds = $fields['product_ids'];

            $order = Order::create($data);
            $this->syncOrderProductAction->handle($order, $productIds);

            return $order;
        });
    }
}
