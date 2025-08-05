<?php

namespace App\View\Composers\Blocks;

use App\Models\Client;
use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class StatsBlockComposers
{
    /**
     * Create a new profile composer.
     */
    public function __construct() {}

    /**
     * Bind data to the view.
     */
    public function compose(View $view): void
    {
        $view->with('items', [
            [
                'name' => 'Всего клиентов',
                'classes' => 'text-orange-500 bg-orange-100',
                'icon' => 'bi bi-person-arms-up',
                'value' => Cache::remember('stats_total_clients', 60 * 60 * 3, function() {
                    return Client::all()->count();
                }),
            ],
            [
                'name' => 'Выполнено заказов',
                'classes' => 'text-green-500 bg-green-100',
                'icon' => 'bi bi-cash',
                'value' => Cache::remember('stats_total_completed_orders', 60 * 60 * 3, function() {
                    return kopToRubView(Order::where('status', Order::STATUS_COMPLETE)->sum('amount_total'));
                }),
            ],
            [
                'name' => 'Заказов за всё время',
                'classes' => 'text-blue-500 bg-blue-100',
                'icon' => 'bi bi-cart2',
                'value' => Cache::remember('stats_total_orders', 60 * 60 * 3, function() {
                    return Order::all()->count();
                }),
            ],
            [
                'name' => 'Заказов в ожидании',
                'classes' => 'text-teal-500 bg-teal-100',
                'icon' => 'bi bi-card-checklist',
                'value' => Cache::remember('stats_total_pending_orders', 60 * 60 * 3, function() {
                    return Order::whereIn('status', [Order::STATUS_PROGRESS, Order::STATUS_ACCEPT])->count();
                }),
            ],
        ]);
    }
}
