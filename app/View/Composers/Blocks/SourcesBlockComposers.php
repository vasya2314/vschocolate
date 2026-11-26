<?php

namespace App\View\Composers\Blocks;

use App\Models\FromPlatform;
use Carbon\Carbon;
use Illuminate\View\View;

/**
 * Источники клиентов за период: новые клиенты и выручка (неотменённые заказы) по площадке клиента.
 */
class SourcesBlockComposers
{
    public function compose(View $view): void
    {
        $data = $view->getData();
        $from = $data['from'] ?? Carbon::now()->startOfMonth();
        $to = $data['to'] ?? Carbon::now()->endOfMonth();

        $periodOrders = fn ($query) => $query->notCanceled()->issuedBetween($from, $to);

        $platforms = FromPlatform::withTrashed()
            ->withCount(['clients as new_clients_count' => fn ($query) => $query->withTrashed()->whereBetween('created_at', [$from, $to])])
            ->withCount(['orders as period_orders_count' => $periodOrders])
            ->withSum(['orders as period_revenue' => $periodOrders], 'amount_total')
            ->get();

        $totalRevenue = (int) $platforms->sum('period_revenue');

        $rows = $platforms
            ->filter(fn (FromPlatform $platform) => $platform->new_clients_count > 0 || $platform->period_orders_count > 0)
            ->sortByDesc('period_revenue')
            ->values()
            ->map(fn (FromPlatform $platform) => [
                'name' => $platform->name,
                'clients' => $platform->new_clients_count,
                'orders' => $platform->period_orders_count,
                'revenue' => (int) $platform->period_revenue,
                'share' => $totalRevenue > 0 ? (int) round($platform->period_revenue / $totalRevenue * 100) : 0,
            ]);

        $view->with([
            'rows' => $rows,
            'maxRevenue' => max(1, (int) $rows->max('revenue')),
            'totalRevenue' => $totalRevenue,
        ]);
    }
}
