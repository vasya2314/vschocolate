<?php

namespace App\View\Composers\Blocks;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Collection;
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
        $debtors = Order::with(['client' => fn ($query) => $query->withTrashed()])
            ->where('status', Order::STATUS_COMPLETE)
            ->underpaid()
            ->get()
            ->groupBy('client_id')
            ->map(fn (Collection $orders) => [
                'client' => $orders->first()->client,
                'count' => $orders->count(),
                'debt' => $orders->sum('debt'),
                'oldest' => Carbon::parse($orders->min('date_issue')),
            ])
            ->filter(fn (array $row) => $row['client'] !== null)
            ->sortByDesc('debt')
            ->values();

        $expected = Order::whereIn('status', [Order::STATUS_ACCEPT, Order::STATUS_PROGRESS])
            ->underpaid()
            ->get(['amount_total', 'amount_payed']);

        $view->with([
            'totalDebt' => $debtors->sum('debt'),
            'debtOrders' => $debtors->sum('count'),
            'debtorsCount' => $debtors->count(),
            'rows' => $debtors->take(self::LIMIT),
            'hidden' => max(0, $debtors->count() - self::LIMIT),
            'expectedDebt' => $expected->sum('debt'),
            'expectedOrders' => $expected->count(),
        ]);
    }
}
