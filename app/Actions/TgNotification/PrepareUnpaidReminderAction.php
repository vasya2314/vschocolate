<?php

namespace App\Actions\TgNotification;

use App\Models\Order;
use Carbon\Carbon;

/**
 * Напоминание о выданных, но не полностью оплаченных заказах.
 */
class PrepareUnpaidReminderAction
{
    private const LIST_LIMIT = 30;

    /**
     * @return string|null null — неоплаченных заказов нет, отправлять нечего
     */
    public function handle(): ?string
    {
        $orders = Order::with(['client' => fn ($q) => $q->withTrashed()])
            ->where('status', Order::STATUS_COMPLETE)
            ->whereColumn('amount_payed', '<', 'amount_total')
            ->orderBy('date_issue')
            ->get();

        if ($orders->isEmpty()) return null;

        $lines = [];
        $lines[] = '💸 <b>Неоплаченные выданные заказы</b>';
        $lines[] = '';

        foreach ($orders->take(self::LIST_LIMIT) as $order) {
            $client = $order->client;
            $lines[] = '• №' . $order->id . ' от ' . Carbon::parse($order->date_issue)->format('d.m')
                . ' — ' . e($client?->name ?? '—')
                . ($client?->phone ? ', ' . e($client->phone_view) : '')
                . ' — долг <b>' . kopToRubView($order->debt) . '</b>';
        }

        if ($orders->count() > self::LIST_LIMIT) {
            $lines[] = '… и ещё ' . ($orders->count() - self::LIST_LIMIT);
        }

        $lines[] = '';
        $lines[] = 'Итого: <b>' . kopToRubView($orders->sum(fn (Order $o) => $o->debt)) . '</b> по ' . $orders->count() . ' зак.';

        return implode("\n", $lines);
    }
}
