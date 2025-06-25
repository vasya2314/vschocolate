<?php

declare(strict_types=1);

namespace App\MoonShine\Layouts;

use MoonShine\Laravel\Layouts\AppLayout;
use MoonShine\ColorManager\ColorManager;
use MoonShine\Contracts\ColorManager\ColorManagerContract;
use MoonShine\UI\Components\{Layout\Layout};
use App\MoonShine\Resources\OrderResource;
use MoonShine\MenuManager\MenuItem;
use App\MoonShine\Resources\ClientResource;

final class MoonShineLayout extends AppLayout
{
    protected function assets(): array
    {
        return [
            ...parent::assets(),
        ];
    }

    protected function menu(): array
    {
        return [
            ...parent::menu(),
            MenuItem::make('Заказы', OrderResource::class)->icon('shopping-cart'),
            MenuItem::make('Клиенты', ClientResource::class)->icon('folder-open'),
        ];
    }

    /**
     * @param ColorManager $colorManager
     */
    protected function colors(ColorManagerContract $colorManager): void
    {
        parent::colors($colorManager);

        // $colorManager->primary('#00000');
    }

    protected function getFooterMenu(): array
    {
        return [];
    }

    protected function getFooterCopyright(): string
    {
        return 'Сделано с любовью для <a href="https://vk.com/id134770711" target="_blank">тебя</a>';
    }

    public function build(): Layout
    {
        return parent::build();
    }
}
