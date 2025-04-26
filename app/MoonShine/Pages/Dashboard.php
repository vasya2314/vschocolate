<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Order;
use MoonShine\Apexcharts\Components\LineChartMetric;
use MoonShine\Laravel\Pages\Page;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Components\Title;

#[\MoonShine\MenuManager\Attributes\SkipMenu]

class Dashboard extends Page
{
    /**
     * @return array<string, string>
     */
    public function getBreadcrumbs(): array
    {
        return [
            '#' => $this->getTitle()
        ];
    }

    public function getTitle(): string
    {
        return $this->title ?: 'Главная';
    }

    /**
     * @return list<ComponentContract>
     */
    protected function components(): iterable
    {
        return [
            Title::make('График заказов за все время', 3)->class('mb-4'),
            LineChartMetric::make('Заказы')
                ->line([
                    'Сумма заказов' => Order::query()
                        ->selectRaw('SUM(amount_total) as sum, DATE_FORMAT(created_at, "%d.%m.%Y") as date')
                        ->groupBy('date')
                        ->pluck('sum', 'date')
                        ->map(function ($sum, $date) {
                            return kopToRub($sum);
                        })
                        ->toArray(),
                ])
                ->withoutSortKeys(),
        ];
    }
}
