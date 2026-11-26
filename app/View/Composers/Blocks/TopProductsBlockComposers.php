<?php

namespace App\View\Composers\Blocks;

use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
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

        $ordersCount = Order::notCanceled()->issuedBetween($from, $to)->count();

        $rows = Product::withTrashed()
            ->withCount(['orders as period_orders_count' => fn ($query) => $query->notCanceled()->issuedBetween($from, $to)])
            ->get()
            ->where('period_orders_count', '>', 0)
            ->sortBy('name')
            ->sortByDesc('period_orders_count')
            ->take(self::LIMIT)
            ->values()
            ->map(fn (Product $product) => [
                'name' => $product->name,
                'count' => $product->period_orders_count,
                'share' => $ordersCount > 0 ? (int) round($product->period_orders_count / $ordersCount * 100) : 0,
            ]);

        $view->with([
            'rows' => $rows,
            'maxCount' => max(1, (int) $rows->max('count')),
            'ordersCount' => $ordersCount,
        ]);
    }
}
