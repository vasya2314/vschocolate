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
        $firstOrder = Order::query()->where('status', '!=', Order::STATUS_CANCELED)->min('date_issue');
        if ($firstOrder && Carbon::parse($firstOrder)->startOfDay()->greaterThan($from)) {
            $from = Carbon::parse($firstOrder)->startOfDay();
        }

        // WEEKDAY(): 0 — понедельник … 6 — воскресенье
        $rows = Order::query()
            ->where('status', '!=', Order::STATUS_CANCELED)
            ->whereBetween('date_issue', [$from, $to])
            ->toBase()
            ->selectRaw('WEEKDAY(date_issue) as wd, COUNT(*) as cnt, SUM(amount_total) as total')
            ->groupBy('wd')
            ->get()
            ->keyBy('wd');

        // Сколько раз каждый день недели встречается в периоде
        $occurrences = array_fill(0, 7, 0);
        foreach (CarbonPeriod::create($from, $to->copy()->startOfDay()) as $day) {
            $occurrences[$day->dayOfWeekIso - 1]++;
        }

        $days = [];
        foreach (self::NAMES as $i => $name) {
            $count = (int) ($rows->get($i)->cnt ?? 0);
            $days[] = [
                'name' => $name,
                'short' => self::SHORT[$i],
                'count' => $count,
                'avg' => $occurrences[$i] > 0 ? $count / $occurrences[$i] : 0,
                'sum' => (int) ($rows->get($i)->total ?? 0),
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
