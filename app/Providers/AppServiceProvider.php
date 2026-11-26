<?php

namespace App\Providers;

use App\View\Composers\Blocks\OrdersChartBlockComposers;
use App\View\Composers\Blocks\DebtsBlockComposers;
use App\View\Composers\Blocks\RepeatClientsBlockComposers;
use App\View\Composers\Blocks\SourcesBlockComposers;
use App\View\Composers\Blocks\StatsBlockComposers;
use App\View\Composers\Blocks\TopProductsBlockComposers;
use App\View\Composers\Blocks\WeekdayLoadBlockComposers;
use App\View\Composers\Blocks\YearRevenueBlockComposers;
use App\View\Composers\Common\SidebarComposers;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('sidebar', SidebarComposers::class);
        View::composer('components.blocks.stats', StatsBlockComposers::class);
        View::composer('components.blocks.orders-chart', OrdersChartBlockComposers::class);
        View::composer('components.blocks.year-revenue', YearRevenueBlockComposers::class);
        View::composer('components.blocks.sources', SourcesBlockComposers::class);
        View::composer('components.blocks.top-products', TopProductsBlockComposers::class);
        View::composer('components.blocks.repeat-clients', RepeatClientsBlockComposers::class);
        View::composer('components.blocks.weekday-load', WeekdayLoadBlockComposers::class);
        View::composer('components.blocks.debts', DebtsBlockComposers::class);
    }
}
