<?php

namespace App\Providers;

use App\View\Composers\Blocks\StatsBlockComposers;
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
    }
}
