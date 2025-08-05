<?php

namespace App\View\Composers\Common;

use Illuminate\View\View;

class SidebarComposers
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
        $view->with('menu', [
            [
                'name' => 'Главная',
                'icon' => 'bi bi-house',
                'href' => route('dashboard.index'),
            ],
            [
                'name' => 'Заказы',
                'icon' => 'bi bi-cart',
                'href' => route('order.index'),
            ],
            [
                'name' => 'Клиенты',
                'icon' => 'bi bi-people',
                'href' => route('client.index'),
            ]
        ]);

        $view->with('adminMenu', [
            [
                'name' => 'Настройки',
                'icon' => 'bi bi-nut',
                'href' => route('settings.index'),
            ],
        ]);
    }
}
