<?php

namespace App\View\Composers\Blocks;

use App\Models\Client;
use App\Models\Order;
use Illuminate\View\View;

/**
 * Долг клиентов за всё время: недоплата по выданным заказам (по клиентам)
 * и ожидаемая доплата по ещё не выданным.
 */
class DebtsBlockComposers
{
    private const LIMIT = 15;

    public function compose(View $view): void
    {
        $debtors = Order::query()
            ->where('status', Order::STATUS_COMPLETE)
            ->whereColumn('amount_payed', '<', 'amount_total')
            ->toBase()
            ->selectRaw('client_id, COUNT(*) as cnt, SUM(amount_total - amount_payed) as debt, MIN(date_issue) as oldest')
            ->groupBy('client_id')
            ->orderByDesc('debt')
            ->get();

        $clients = Client::withTrashed()
            ->whereIn('id', $debtors->take(self::LIMIT)->pluck('client_id'))
            ->get()
            ->keyBy('id');

        $expected = Order::query()
            ->whereIn('status', [Order::STATUS_ACCEPT, Order::STATUS_PROGRESS])
            ->whereColumn('amount_payed', '<', 'amount_total')
            ->toBase()
            ->selectRaw('COUNT(*) as cnt, COALESCE(SUM(amount_total - amount_payed), 0) as debt')
            ->first();

        $view->with([
            'totalDebt' => (int) $debtors->sum('debt'),
            'debtOrders' => (int) $debtors->sum('cnt'),
            'debtorsCount' => $debtors->count(),
            'rows' => $debtors->take(self::LIMIT)->map(fn ($row) => [
                'client' => $clients->get($row->client_id),
                'count' => (int) $row->cnt,
                'debt' => (int) $row->debt,
                'oldest' => \Carbon\Carbon::parse($row->oldest),
            ])->filter(fn ($row) => $row['client'] !== null)->values(),
            'hidden' => max(0, $debtors->count() - self::LIMIT),
            'expectedDebt' => (int) $expected->debt,
            'expectedOrders' => (int) $expected->cnt,
        ]);
    }
}
