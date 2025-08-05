<?php

namespace App\Actions\Order;

use App\Actions\Client\StoreClientAction;
use App\Exceptions\BusinessException;
use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class GroupStoreOrderAndClientAction
{
    public function __construct(
        protected StoreClientAction $storeClientAction,
        protected StoreOrderAction $storeOrderAction
    ) {}

    public function handle(array $orderFields, array $clientFields): ?Order
    {
        return DB::transaction(function () use ($orderFields, $clientFields) {
            if(isset($clientFields['id']))
            {
                $client = Client::find($clientFields['id']);

                if(!$client) throw new BusinessException('Клиент не найден. Либо его не существует, либо он в архиве');
            } else {
                $client = $this->storeClientAction->handle($clientFields);
            }

            $orderFields['client_id'] = $client->id;

            return $this->storeOrderAction->handle($orderFields);
        });
    }
}
