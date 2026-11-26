<?php

namespace App\View\Composers\Blocks;

use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\View\View;

/**
 * Нагрузка по дням недели за 12 месяцев, заканчивая выбранным месяцем
 * (или с первого заказа, если он позже): сколько неотменённых заказов в среднем приходится на один такой день.
 */
class WeekdayLoadBlockComposers
{
    private const NAMES = ['Понедельник', 'Вторник', 'Среда', 'Четверг', 'Пятница', 'Суббота', 'Воскресенье'];
    private const SHORT = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

    public function compose(View $view): void
    {
        $to = ($view->getData()['to'] ?? Carbon::now()->endOfMonth())->copy()->endOfDay();
        $from = $to->copy()->startOfMonth()->subMonthsNoOverflow(11);

        // Не размываем среднее днями до первого заказа
        $firstOrder = Order::notCanceled()->min('date_issue');
        if ($firstOrder && Carbon::parse($firstOrder)->startOfDay()->greaterThan($from)) {
            $from = Carbon::parse($firstOrder)->startOfDay();
        }

        // 0 — понедельник … 6 — воскресенье
        $byWeekday = Order::notCanceled()
            ->issuedBetween($from, $to)
            ->get(['date_issue', 'amount_total'])
            ->groupBy(fn (Order $order) => Carbon::parse($order->date_issue)->dayOfWeekIso - 1);

        // Сколько раз каждый день недели встречается в периоде
        $occurrences = array_fill(0, 7, 0);
        foreach (CarbonPeriod::create($from, $to->copy()->startOfDay()) as $day) {
            $occurrences[$day->dayOfWeekIso - 1]++;
        }

        $days = [];
        foreach (self::NAMES as $i => $name) {
            $orders = $byWeekday->get($i, collect());
            $count = $orders->count();
            $days[] = [
                'name' => $name,
                'short' => self::SHORT[$i],
                'count' => $count,
                'avg' => $occurrences[$i] > 0 ? $count / $occurrences[$i] : 0,
                'sum' => (int) $orders->sum('amount_total'),
                'is_weekend' => $i >= 5,
            ];
        }

        $maxAvg = max(array_column($days, 'avg')) ?: 1;

        $view->with([
            'days' => $days,
            'maxAvg' => $maxAvg,
            'busiest' => $maxAvg > 0 ? collect($days)->sortByDesc('avg')->first()['name'] : null,
            'periodLabel' => $from->format('d.m.Y') . ' — ' . $to->format('d.m.Y'),
            'total' => array_sum(array_column($days, 'count')),
        ]);
    }
}
