<?php

namespace App\Actions\Order;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class UpdateOrderAction
{
    public function __construct(
        protected SyncOrderProductAction $syncOrderProductAction,
    ) {}

    public function handle(array $fields, Order $order): Order
    {
        return DB::transaction(function () use ($fields, $order) {
            $data = [];

            if(array_key_exists('status', $fields)) $data['status'] = $fields['status'];
            if(array_key_exists('date_issue', $fields)) $data['date_issue'] = Carbon::parse($fields['date_issue'])->format('Y-m-d H:i:s');
            if(array_key_exists('description', $fields)) $data['description'] = $fields['description'];
            if(array_key_exists('amount_total', $fields)) $data['amount_total'] = $fields['amount_total'];
            if(array_key_exists('amount_payed', $fields)) $data['amount_payed'] = $fields['amount_payed'];

            $order->update($data);

            if(array_key_exists('product_ids', $fields))
            {
                $this->syncOrderProductAction->handle($order, $fields['product_ids']);
            }

            return $order->refresh();
        });

    }
}
