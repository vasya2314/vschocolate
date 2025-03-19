<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Models\Order;
use App\Models\OrderContent;
use App\MoonShine\Pages\FormOrder;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Pages\Crud\DetailPage;
use MoonShine\Laravel\Pages\Crud\IndexPage;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<Order>
 */
class OrderResource extends ModelResource
{
    protected string $model = Order::class;

    protected string $title = 'Заказы';

    protected array $with = ['client', 'contents'];

    protected int $itemsPerPage = 50;

    protected bool $columnSelection = true;

    protected function activeActions(): ListOf
    {
        return parent::activeActions()
            ->except(Action::VIEW);
    }

    protected function pages(): array
    {
        return [
            IndexPage::class,
            FormOrder::class,
            DetailPage::class,
        ];
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->customWrapperAttributes(['style' => 'position: sticky; left: 0; z-index: 10; background: inherit;']),
            Date::make('Дата выдачи', 'date_issue')->format('d.m.Y H:i'),

            Text::make('Клиент')->changePreview(function ($value, $ctx) {
                $client = $ctx->getData()->getOriginal()->client;

                return $client->name . ' | ' . '<a href="tel:' . $client->phone .'">' . $client->phone_view . '</a>';
            }),


            Text::make('Что в заказе')->changePreview(function ($value, $ctx) {
                $contents = $ctx->getData()->getOriginal()->contents;
                $result = [];

                if(!empty($contents))
                {
                    foreach ($contents as $content)
                    {
                        $result[] = $content->content_view;
                    }

                    return implode('<br>', $result);
                }

                return '-';
            }),
            Textarea::make('Описание', 'short_description'),
            Text::make('Сумма заказа', 'amount_total_view'),
            Text::make('Оплаченная сумма', 'amount_payed_view'),
            Text::make('Статус', 'status_view'),
            Date::make('Дата создания', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [];
    }

    /**
     * @return list<FieldContract>
     */
    protected function detailFields(): iterable
    {
        return [
            ID::make(),
        ];
    }

    /**
     * @param Order $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        return [];
    }

    protected function search(): array
    {
        return ['id', 'client.name'];
    }

    protected function filters(): iterable
    {
        return [
            Select::make('Статус', 'status')
                ->options(Order::statuses())
                ->multiple(),

            Select::make('Что в заказе', 'contents')
                ->options(OrderContent::contents())
                ->multiple()
                ->onApply(function($item, $value, $field) {

                    if(!empty($value))
                    {
                        $item->whereHas('contents', function ($query) use ($value) {
                            $query->whereIn('key', $value);
                        });
                    }

                    return $item;
                }),
        ];
    }

}
