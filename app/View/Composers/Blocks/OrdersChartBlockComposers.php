<?php

namespace App\View\Composers\Blocks;

use App\Models\Order;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\View\View;

class OrdersChartBlockComposers
{
    public function compose(View $view): void
    {
        $data = $view->getData();
        $from = ($data['from'] ?? Carbon::now()->startOfMonth())->copy()->startOfDay();
        $to = ($data['to'] ?? Carbon::now()->endOfMonth())->copy()->endOfDay();

        $orders = Order::query()
            ->where('status', '!=', Order::STATUS_CANCELED)
            ->whereBetween('date_issue', [$from, $to])
            ->get(['date_issue', 'amount_total'])
            ->groupBy(fn (Order $order) => Carbon::parse($order->date_issue)->format('Y-m-d'));

        $today = Carbon::today()->format('Y-m-d');
        $days = [];

        foreach (CarbonPeriod::create($from, $to->copy()->startOfDay()) as $date) {
            $key = $date->format('Y-m-d');
            $dayOrders = $orders->get($key, collect());

            $days[] = [
                'day' => $date->day,
                'label' => $date->copy()->locale('ru')->isoFormat('D MMMM, dd'),
                'count' => $dayOrders->count(),
                'sum' => kopToRubView((int) round($dayOrders->sum('amount_total'))),
                'is_today' => $key === $today,
                'is_weekend' => $date->isWeekend(),
            ];
        }

        $counts = array_column($days, 'count');
        $max = max($counts ?: [0]);
        $scaleMax = max(4, (int) ceil($max / 4) * 4);

        $view->with([
            'days' => $days,
            'scaleMax' => $scaleMax,
            'totalCount' => array_sum($counts),
            'totalSum' => kopToRubView((int) round($orders->flatten()->sum('amount_total'))),
        ]);
    }
}
