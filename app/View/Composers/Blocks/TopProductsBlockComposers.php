<?php

namespace App\View\Composers\Blocks;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Популярные товары за период: в скольких неотменённых заказах встречается товар.
 * Количество и цена товара в заказе не хранятся, поэтому считаем именно заказы.
 */
class TopProductsBlockComposers
{
    private const LIMIT = 10;

    public function compose(View $view): void
    {
        $data = $view->getData();
        $from = $data['from'] ?? Carbon::now()->startOfMonth();
        $to = $data['to'] ?? Carbon::now()->endOfMonth();

        $base = fn () => Order::query()
            ->where('status', '!=', Order::STATUS_CANCELED)
            ->whereBetween('date_issue', [$from, $to]);

        $ordersCount = $base()->count();

        $rows = DB::table('order_product')
            ->join('products', 'products.id', '=', 'order_product.product_id')
            ->whereIn('order_product.order_id', $base()->select('orders.id'))
            ->groupBy('products.id', 'products.name')
            ->orderByDesc('cnt')
            ->orderBy('products.name')
            ->limit(self::LIMIT)
            ->get(['products.name', DB::raw('COUNT(*) as cnt')])
            ->map(fn ($row) => [
                'name' => $row->name,
                'count' => (int) $row->cnt,
                'share' => $ordersCount > 0 ? (int) round($row->cnt / $ordersCount * 100) : 0,
            ]);

        $view->with([
            'rows' => $rows,
            'maxCount' => max(1, (int) $rows->max('count')),
            'ordersCount' => $ordersCount,
        ]);
    }
}
