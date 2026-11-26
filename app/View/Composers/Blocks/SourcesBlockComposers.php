<?php

namespace App\View\Composers\Blocks;

use App\Models\Client;
use App\Models\FromPlatform;
use App\Models\Order;
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

        $newClients = Client::whereBetween('created_at', [$from, $to])
            ->toBase()
            ->selectRaw('from_platform_id, COUNT(*) as cnt')
            ->groupBy('from_platform_id')
            ->pluck('cnt', 'from_platform_id');

        $orders = Order::query()
            ->join('clients', 'clients.id', '=', 'orders.client_id')
            ->where('orders.status', '!=', Order::STATUS_CANCELED)
            ->whereBetween('orders.date_issue', [$from, $to])
            ->toBase()
            ->selectRaw('clients.from_platform_id, COUNT(*) as cnt, SUM(orders.amount_total) as total')
            ->groupBy('clients.from_platform_id')
            ->get()
            ->keyBy('from_platform_id');

        $totalRevenue = (int) $orders->sum('total');

        $rows = FromPlatform::withTrashed()->get()
            ->map(fn (FromPlatform $platform) => [
                'name' => $platform->name,
                'clients' => (int) ($newClients[$platform->id] ?? 0),
                'orders' => (int) ($orders->get($platform->id)->cnt ?? 0),
                'revenue' => (int) ($orders->get($platform->id)->total ?? 0),
            ])
            ->filter(fn ($row) => $row['clients'] > 0 || $row['orders'] > 0)
            ->sortByDesc('revenue')
            ->values()
            ->map(fn ($row) => $row + [
                'share' => $totalRevenue > 0 ? (int) round($row['revenue'] / $totalRevenue * 100) : 0,
            ]);

        $view->with([
            'rows' => $rows,
            'maxRevenue' => max(1, (int) $rows->max('revenue')),
            'totalRevenue' => $totalRevenue,
        ]);
    }
}
