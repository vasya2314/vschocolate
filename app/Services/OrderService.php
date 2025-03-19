<?php

namespace App\Services;

use App\DTO\OrderDTO;
use App\Models\Order;
use App\Models\OrderContent;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function createOrder(OrderDTO $createOrderDTO): Order
    {
        $data = [
            'date_issue' => $createOrderDTO->getDateIssue(),
            'client_id' => $createOrderDTO->getClientId(),
            'description' => $createOrderDTO->getDescription(),
            'amount_total' => $createOrderDTO->getAmountTotal(),
            'amount_payed' => $createOrderDTO->getAmountPayed(),
            'status' => $createOrderDTO->getStatus(),
        ];

        $order = new Order($data);
        $order->save();

        if(!empty($createOrderDTO->getContents()))
        {
            foreach($createOrderDTO->getContents() as $contentKey)
            {
                OrderContent::create(
                    [
                        'key' => $contentKey,
                        'order_id' => $order->id,
                    ]
                );
            }
        }

        return $order;
    }

    public function updateOrder(Order $order, OrderDTO $orderDTO): Order
    {
        return DB::transaction(function () use ($orderDTO, $order) {
            $data = [];

            if ($orderDTO->getDateIssue() !== null)
            {
                $data['date_issue'] = $orderDTO->getDateIssue();
            }

            if ($orderDTO->getDescription() !== null)
            {
                $data['description'] = $orderDTO->getDescription();
            }

            if ($orderDTO->getAmountTotal() !== null)
            {
                $data['amount_total'] = $orderDTO->getAmountTotal();
            }

            if ($orderDTO->getAmountPayed() !== null)
            {
                $data['amount_payed'] = $orderDTO->getAmountPayed();
            }

            if ($orderDTO->getStatus() !== null)
            {
                $data['status'] = $orderDTO->getStatus();
            }

            if(!empty($orderDTO->getContents()))
            {
                $this->syncContent($order, $orderDTO->getContents());
            }

            if (!empty($data)) $order->update($data);

            return $order;
        });
    }

    private function syncContent(Order $order, array $newContent): void
    {
        $content = $order->contents->pluck('id', 'key')->toArray();

        if(!empty($newContent))
        {
            foreach($newContent as $newContentItemKey => $newContentItemValue)
            {
                if(isset($content[$newContentItemValue]))
                {
                    unset($content[$newContentItemValue]);
                    unset($newContent[$newContentItemKey]);
                }
            }
        }

        if(!empty($newContent))
        {
            foreach($newContent as $newItem)
            {
                OrderContent::create([
                    'key' => $newItem,
                    'order_id' => $order->id,
                ]);
            }
        }

        if(!empty($content))
        {
            $contentIds = array_values($content);

            OrderContent::whereIn('id', $contentIds)->delete();
        }
    }

}
