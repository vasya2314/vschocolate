<?php

namespace App\Actions\TgNotification;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * Утреннее сообщение: заказы к выдаче на ближайшие дни и кнопки смены статуса под ним.
 */
class PrepareMessageAction
{
    /** Префикс callback_data кнопки смены статуса: st|{orderId}|{status}|{Ymd начала периода} */
    public const CALLBACK_PREFIX = 'st';

    /**
     * @param int $days количество дней, начиная с $from (последний день не включается)
     * @return array{text: string, keyboard: array}
     */
    public function handle(int $days, ?Carbon $from = null): array
    {
        $from = ($from ?? Carbon::now())->copy()->startOfDay();
        $to = $from->copy()->addDays($days);

        $orders = Order::with(['client', 'products'])
            ->whereBetween('date_issue', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date_issue', 'ASC')
            ->get();

        return [
            'text' => $this->buildText($orders),
            'keyboard' => $this->buildKeyboard($orders, $from),
        ];
    }

    private function buildText(Collection $orders): string
    {
        if ($orders->isEmpty()) return "Заказов к выдаче не найдено :(";

        $data = $orders->groupBy(fn (Order $order) => Carbon::parse($order->date_issue)->format('d.m.Y'));

        $message = "";
        foreach ($data as $date => $row) {
            $message .= "<b>" . $date . ":" . "</b>\n";

            foreach ($row as $order) {
                $message .= "🛒 | Заказ №" . $order->id . " (" . e($this->getProducts($order->products)) . ") \n";
                $message .= "Сумма: " . kopToRubView($order->amount_total) . ". (оплачено: " . kopToRubView($order->amount_payed) . ")" . "\n";
                $message .= "Статус: " . $order->status_view . "\n";

                if ($row->last() !== $order) $message .= "\n";
            }

            if ($data->last() !== $row) {
                $message .= "—————————————\n";
            }
        }

        return $message;
    }

    /**
     * Кнопки только для заказов, которые ещё не выданы и не отменены.
     */
    private function buildKeyboard(Collection $orders, Carbon $from): array
    {
        $keyboard = [];

        foreach ($orders as $order) {
            $buttons = [];

            if ($order->status === Order::STATUS_ACCEPT) {
                $buttons[] = $this->button('✅ №' . $order->id . ' Выполнен', $order, Order::STATUS_PROGRESS, $from);
            }

            if (in_array($order->status, [Order::STATUS_ACCEPT, Order::STATUS_PROGRESS], true)) {
                $buttons[] = $this->button('📦 №' . $order->id . ' Выдан', $order, Order::STATUS_COMPLETE, $from);
            }

            if ($buttons) $keyboard[] = $buttons;
        }

        return $keyboard;
    }

    private function button(string $text, Order $order, string $status, Carbon $from): array
    {
        return [
            'text' => $text,
            'callback_data' => implode('|', [self::CALLBACK_PREFIX, $order->id, $status, $from->format('Ymd')]),
        ];
    }

    private function getProducts(Collection $products): string
    {
        return $products->pluck('name')->implode(', ');
    }
}
