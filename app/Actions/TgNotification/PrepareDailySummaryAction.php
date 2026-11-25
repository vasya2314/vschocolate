<?php

namespace App\Actions\TgNotification;

use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;

/**
 * Вечерний итог дня: что выдано, что осталось, новые заказы и план на завтра.
 */
class PrepareDailySummaryAction
{
    /** Сколько невыданных заказов перечислять поимённо */
    private const LIST_LIMIT = 15;

    public function handle(?Carbon $date = null): string
    {
        $day = ($date ?? Carbon::today())->copy()->startOfDay();
        $period = [$day->copy(), $day->copy()->endOfDay()];
        $tomorrow = [$day->copy()->addDay(), $day->copy()->addDay()->endOfDay()];

        $orders = Order::with('client')
            ->whereBetween('date_issue', $period)
            ->orderBy('date_issue')
            ->get();

        $completed = $orders->where('status', Order::STATUS_COMPLETE);
        $pending = $orders->whereIn('status', [Order::STATUS_ACCEPT, Order::STATUS_PROGRESS]);
        $canceled = $orders->where('status', Order::STATUS_CANCELED);

        $created = Order::whereBetween('created_at', $period)->get(['amount_total']);
        $newClients = Client::whereBetween('created_at', $period)->count();

        $tomorrowOrders = Order::whereBetween('date_issue', $tomorrow)
            ->where('status', '!=', Order::STATUS_CANCELED)
            ->get(['amount_total']);

        $lines = [];
        $lines[] = '🌙 <b>Итог дня — ' . $day->format('d.m.Y') . '</b>';
        $lines[] = '';
        $lines[] = '📦 Выдано: <b>' . $completed->count() . '</b> на ' . $this->rub($completed->sum('amount_total'));

        $completedDebt = $completed->sum(fn (Order $o) => $o->debt);
        if ($completedDebt > 0) {
            $lines[] = '💸 Из них не доплачено: ' . $this->rub($completedDebt);
        }

        $lines[] = '⏳ Не выдано: <b>' . $pending->count() . '</b>';
        foreach ($pending->take(self::LIST_LIMIT) as $order) {
            $lines[] = '   • №' . $order->id . ' ' . e($order->client?->name ?? '—') . ' — ' . $order->status_view;
        }
        if ($pending->count() > self::LIST_LIMIT) {
            $lines[] = '   … и ещё ' . ($pending->count() - self::LIST_LIMIT);
        }

        if ($canceled->isNotEmpty()) {
            $lines[] = '❌ Отменено: ' . $canceled->count();
        }

        $lines[] = '';
        $lines[] = '🆕 Принято новых заказов: <b>' . $created->count() . '</b> на ' . $this->rub($created->sum('amount_total'));
        $lines[] = '👤 Новых клиентов: ' . $newClients;
        $lines[] = '';
        $lines[] = '📅 Завтра к выдаче: <b>' . $tomorrowOrders->count() . '</b> на ' . $this->rub($tomorrowOrders->sum('amount_total'));

        return implode("\n", $lines);
    }

    private function rub(float|int $kop): string
    {
        return kopToRubView((int) round($kop));
    }
}
