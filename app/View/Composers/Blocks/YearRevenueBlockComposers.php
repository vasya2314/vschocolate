<?php

namespace App\View\Composers\Blocks;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\View\View;

/**
 * Выручка по месяцам за год выбранного месяца (неотменённые заказы по дате выдачи).
 */
class YearRevenueBlockComposers
{
    public function compose(View $view): void
    {
        $selected = ($view->getData()['from'] ?? Carbon::now())->copy()->startOfMonth();
        $yearStart = $selected->copy()->startOfYear();

        $byMonth = Order::notCanceled()
            ->issuedBetween($yearStart, $yearStart->copy()->endOfYear())
            ->get(['date_issue', 'amount_total'])
            ->groupBy(fn (Order $order) => Carbon::parse($order->date_issue)->month);

        $currentMonth = Carbon::now()->format('Y-m');
        $months = [];
        foreach (range(1, 12) as $m) {
            $date = $yearStart->copy()->month($m);
            $orders = $byMonth->get($m, collect());
            $months[] = [
                'short' => $date->copy()->locale('ru')->isoFormat('MMM'),
                'label' => $date->copy()->locale('ru')->isoFormat('MMMM YYYY'),
                'sum' => (int) $orders->sum('amount_total'),
                'count' => $orders->count(),
                'is_selected' => $date->format('Y-m') === $selected->format('Y-m'),
                'is_current' => $date->format('Y-m') === $currentMonth,
                'url' => route('dashboard.index', ['month' => $date->format('Y-m')]),
            ];
        }

        $sums = array_column($months, 'sum');
        $view->with([
            'year' => $yearStart->year,
            'months' => $months,
            'scaleMax' => $this->niceMax(max($sums ?: [0])),
            'yearSum' => array_sum($sums),
        ]);
    }

    /** Верх шкалы: «круглое» число ≥ максимума, делящееся на 4 шага */
    private function niceMax(int $max): int
    {
        if ($max <= 0) return 400_000; // 4 000 ₽ — пустая шкала

        $step = $max / 4;
        $magnitude = 10 ** floor(log10($step));
        foreach ([1, 2, 2.5, 5, 10] as $k) {
            if ($k * $magnitude >= $step) return (int) ($k * $magnitude * 4);
        }

        return (int) (10 * $magnitude * 4);
    }
}
