<?php

namespace App\Actions\TgNotification;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class PrepareMessageAction
{
    public function handle(int $days): string
    {
        $message = "";

        $now = Carbon::now();
        $nowString = $now->toDateString();

        $nowPlusString = $now->addDays($days)->toDateString();

        $data = Order::with(['client', 'products'])
            ->whereBetween('date_issue', [$nowString, $nowPlusString])
            ->orderBy('date_issue', 'ASC')
            ->get()
            ->mapToGroups(function ($item) {
                $carbonDate = Carbon::parse($item->date_issue);
                $carbonDateString = $carbonDate->format('d.m.Y');

                return [$carbonDateString => $item];
            });

        if($data->isEmpty())
        {
            $message = "Заказов к выдаче не найдено :(";
        } else {
            foreach ($data as $date => $row)
            {
                $message .= "<b>" . $date . ":" . "</b>\n";

                if($row->isEmpty())
                {
                    $message .= "Заказов не найдено :( \n";
                } else {
                    foreach($row as $order)
                    {
                        $message .= "🛒 | Заказ №" . $order->id . " (" . $this->getProducts($order->products) . ") \n";
                        $message .= "Сумма: " . kopToRubView($order->amount_total) . ". (оплачено: " . kopToRubView($order->amount_payed) . ")" . "\n";
                        $message .= "Статус: " . $order->status_view . "\n";

                        if($row->last() !== $order) $message .= "\n";
                    }
                }

                if($data->last() !== $row)
                {
                    $message .= "—————————————\n";
                }
            }
        }

        return $message;
    }

    private function getProducts(Collection $products): string
    {
        $result = [];

        if($products->isNotEmpty())
        {
            foreach ($products as $product) $result[] = $product->name;

            return implode(', ', $result);
        }

        return '';
    }
}
