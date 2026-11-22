<?php

namespace App\View\Composers\Blocks;

use App\Models\Client;
use App\Models\Order;
use Carbon\Carbon;
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
        $data = $view->getData();
        $from = $data['from'] ?? Carbon::now()->startOfDay();
        $to = $data['to'] ?? Carbon::now()->endOfDay();
        $period = [$from, $to];

        $view->with('items', [
            [
                'name' => 'Новых клиентов',
                'classes' => 'text-orange-500 bg-orange-100',
                'icon' => 'bi bi-person-arms-up',
                'value' => Client::whereBetween('created_at', $period)->count(),
            ],
            [
                'name' => 'Выдано заказов на сумму',
                'classes' => 'text-green-500 bg-green-100',
                'icon' => 'bi bi-cash',
                'value' => kopToRubView(
                    Order::where('status', Order::STATUS_COMPLETE)
                        ->whereBetween('date_issue', $period)
                        ->sum('amount_total')
                ),
            ],
            [
                'name' => 'Заказов к выдаче',
                'classes' => 'text-blue-500 bg-blue-100',
                'icon' => 'bi bi-cart2',
                'value' => Order::whereBetween('date_issue', $period)->count(),
            ],
            [
                'name' => 'Заказов в ожидании',
                'classes' => 'text-teal-500 bg-teal-100',
                'icon' => 'bi bi-card-checklist',
                'value' => Order::whereIn('status', [Order::STATUS_PROGRESS, Order::STATUS_ACCEPT])
                    ->whereBetween('date_issue', $period)
                    ->count(),
            ],
        ]);
    }
}
