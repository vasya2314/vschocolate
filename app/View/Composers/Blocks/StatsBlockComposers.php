<?php

namespace App\View\Composers\Blocks;

use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\View\View;

/**
 * Плашки за период и изменение к предыдущему периоду (для месяца — к прошлому месяцу целиком).
 */
class StatsBlockComposers
{
    public function compose(View $view): void
    {
        $data = $view->getData();
        $from = ($data['from'] ?? Carbon::now()->startOfDay())->copy();
        $to = ($data['to'] ?? Carbon::now()->endOfDay())->copy();
        [$prevFrom, $prevTo] = $this->previousPeriod($from, $to);

        $cur = $this->metrics($from, $to);
        $prev = $this->metrics($prevFrom, $prevTo);
        $prevLabel = $this->periodLabel($prevFrom, $prevTo);

        $view->with('items', [
            [
                'name' => 'Новых клиентов',
                'classes' => 'text-orange-500 bg-orange-100',
                'icon' => 'bi bi-person-arms-up',
                'value' => $cur['new_clients'],
                'delta' => $this->percentDelta($cur['new_clients'], $prev['new_clients'], $prev['new_clients'], $prevLabel),
            ],
            [
                'name' => 'Выдано заказов на сумму',
                'classes' => 'text-green-500 bg-green-100',
                'icon' => 'bi bi-cash',
                'value' => kopToRubView($cur['completed_sum']),
                'delta' => $this->percentDelta($cur['completed_sum'], $prev['completed_sum'], kopToRubView($prev['completed_sum']), $prevLabel),
            ],
            [
                'name' => 'Заказов к выдаче',
                'classes' => 'text-blue-500 bg-blue-100',
                'icon' => 'bi bi-cart2',
                'value' => $cur['active'],
                'delta' => $this->percentDelta($cur['active'], $prev['active'], $prev['active'], $prevLabel),
            ],
            [
                'name' => 'Средний чек',
                'classes' => 'text-purple-500 bg-purple-100',
                'icon' => 'bi bi-receipt',
                'value' => kopToRubView($cur['avg_check']),
                'delta' => $this->percentDelta($cur['avg_check'], $prev['avg_check'], kopToRubView($prev['avg_check']), $prevLabel),
            ],
            [
                'name' => 'Заказов в ожидании',
                'classes' => 'text-teal-500 bg-teal-100',
                'icon' => 'bi bi-card-checklist',
                'value' => $cur['pending'],
                'delta' => null,
            ],
            [
                'name' => 'Доля отмен',
                'classes' => 'text-red-500 bg-red-100',
                'icon' => 'bi bi-x-circle',
                'value' => $this->formatPercent($cur['cancel_share']) . ' (' . $cur['canceled'] . ' из ' . $cur['total'] . ')',
                'delta' => $this->pointsDelta($cur['cancel_share'], $prev['cancel_share'], $prev['total'] > 0, $prevLabel),
            ],
        ]);
    }

    /**
     * Все показатели по заказам периода (по дате выдачи).
     */
    private function metrics(Carbon $from, Carbon $to): array
    {
        $orders = Order::issuedBetween($from, $to)->get(['status', 'amount_total']);
        $active = $orders->where('status', '!=', Order::STATUS_CANCELED);

        $total = $orders->count();
        $canceled = $total - $active->count();

        return [
            'new_clients' => Client::whereBetween('created_at', [$from, $to])->count(),
            'total' => $total,
            'canceled' => $canceled,
            'active' => $active->count(),
            'pending' => $orders->whereIn('status', [Order::STATUS_ACCEPT, Order::STATUS_PROGRESS])->count(),
            'completed_sum' => (int) $orders->where('status', Order::STATUS_COMPLETE)->sum('amount_total'),
            'avg_check' => (int) round($active->avg('amount_total') ?? 0),
            'cancel_share' => $total > 0 ? $canceled / $total * 100 : 0.0,
        ];
    }

    /**
     * Целый месяц → предыдущий месяц целиком; иначе — такой же по длине период прямо перед текущим.
     */
    private function previousPeriod(Carbon $from, Carbon $to): array
    {
        $isMonth = $from->equalTo($from->copy()->startOfMonth())
            && $to->format('Y-m-d') === $from->copy()->endOfMonth()->format('Y-m-d');

        if ($isMonth) {
            $prevFrom = $from->copy()->subMonthNoOverflow()->startOfMonth();

            return [$prevFrom, $prevFrom->copy()->endOfMonth()];
        }

        $length = $from->diffInSeconds($to);
        $prevTo = $from->copy()->subSecond();

        return [$prevTo->copy()->subSeconds($length), $prevTo];
    }

    private function periodLabel(Carbon $from, Carbon $to): string
    {
        if ($from->format('Y-m') === $to->format('Y-m') && $from->day === 1) {
            return $from->copy()->locale('ru')->isoFormat('MMMM YYYY');
        }

        return $from->format('d.m.Y') . ' — ' . $to->format('d.m.Y');
    }

    /**
     * Изменение в процентах. Рост — хорошо (зелёный).
     */
    private function percentDelta(int|float $current, int|float $previous, int|string $previousView, string $prevLabel): ?array
    {
        if ($previous == 0) {
            return ['text' => '—', 'tone' => 'neutral', 'title' => 'Не с чем сравнить: за ' . $prevLabel . ' — ' . $previousView];
        }

        $percent = (int) round(($current - $previous) / $previous * 100);

        return [
            'text' => $this->arrow($percent) . ' ' . abs($percent) . '%',
            'tone' => $percent > 0 ? 'good' : ($percent < 0 ? 'bad' : 'neutral'),
            'title' => 'За ' . $prevLabel . ': ' . $previousView,
        ];
    }

    /**
     * Изменение доли: показываем прошлое значение. Рост доли отмен — плохо (красный).
     */
    private function pointsDelta(float $current, float $previous, bool $hasPrevious, string $prevLabel): ?array
    {
        if (!$hasPrevious) {
            return ['text' => '—', 'tone' => 'neutral', 'title' => 'Не с чем сравнить: за ' . $prevLabel . ' заказов нет'];
        }

        $diff = round($current - $previous, 1);

        // «▼ было 7,8% в прошлом месяце» — понятнее, чем разница в процентных пунктах
        return [
            'text' => $this->arrow($diff) . ' было ' . $this->formatPercent($previous),
            'suffix' => 'в прошлом месяце',
            'tone' => $diff > 0 ? 'bad' : ($diff < 0 ? 'good' : 'neutral'),
            'title' => 'За ' . $prevLabel . ': ' . $this->formatPercent($previous)
                . ' (изменение: ' . ($diff > 0 ? '+' : ($diff < 0 ? '−' : '')) . $this->formatNumber(abs($diff)) . ' п.п.)',
        ];
    }

    private function arrow(int|float $value): string
    {
        return $value > 0 ? '▲' : ($value < 0 ? '▼' : '=');
    }

    private function formatPercent(float $value): string
    {
        return $this->formatNumber(round($value, 1)) . '%';
    }

    private function formatNumber(float $value): string
    {
        return rtrim(rtrim(number_format($value, 1, ',', ''), '0'), ',');
    }
}
