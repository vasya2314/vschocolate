<?php

declare(strict_types=1);

namespace App\MoonShine\Pages;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderContent;
use MoonShine\AssetManager\Js;
use MoonShine\Laravel\Pages\Crud\FormPage;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\Number;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;
use Throwable;


class FormOrder extends FormPage
{
    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function fields(): iterable
    {
        return [];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function topLayer(): array
    {
        return [
            ...parent::topLayer()
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function mainLayer(): array
    {
        $resource = $this->getResource();
        $isItemExist = $resource->isItemExists();

        if($isItemExist) {
            $item = $resource->getItem()->load(['client', 'contents']);

            return [
                FormBuilder::make()
                    ->action(route('order.update', ['order' => $item->id]))
                    ->method(FormMethod::POST)
                    ->fields([
                        Box::make([
                            Phone::make('Телефон клиента', 'client_phone')
                                ->required()
                                ->customAttributes(['id' => 'phone-input'])
                                ->mask('+7 (999) 999-99-99')
                                ->disabled(),
                            Text::make('Имя клиента', 'client_name')
                                ->customAttributes(['id' => 'name-input'])
                                ->disabled()
                        ]),

                        Box::make([
                            Hidden::make('_method')->setValue('patch'),
                            Select::make('Что в заказе', 'contents')
                                ->options(OrderContent::contents())
                                ->multiple()
                                ->required(),
                            Select::make('Статус', 'status')
                                ->options(Order::statuses())
                                ->required(),
                            Date::make('Дата выдачи', 'date_issue')
                                ->withTime()
                                ->required(),
                            Textarea::make('Описание заказа', 'description')->required(),
                            Number::make('Сумма заказа', 'amount_total')->required(),
                            Number::make('Оплаченная сумма', 'amount_payed')->required(),
                        ]),
                    ])
                    ->fill([
                        'client_phone' => $item->client->phone ?? '',
                        'client_name' => $item->client->name ?? '',
                        'contents' => $item->contents->pluck('key')->toArray() ?? [],
                        'date_issue' => $item->date_issue,
                        'description' => $item->description,
                        'amount_total' => kopToRub($item->amount_total),
                        'amount_payed' => kopToRub($item->amount_payed),
                        'status' => $item->status,
                    ])
                    ->submit(__('moonshine::ui.save'), ['class' => 'btn-primary btn-lg'])
                    ->async(),
            ];
        }

        return [
            FormBuilder::make()
                ->action(route('order.store'))
                ->method(FormMethod::POST)
                ->fields([
                    Box::make([
                        Phone::make('Телефон клиента', 'client_phone')
                            ->required()
                            ->customAttributes(['id' => 'phone-input'])
                            ->mask('+7 (999) 999-99-99'),
                        Text::make('Имя клиента', 'client_name')
                            ->customAttributes(['id' => 'name-input'])
                            ->required()
                            ->customWrapperAttributes([
                                'class' => 'hidden',
                            ]),
                        Select::make('Откуда', 'client_from')
                            ->customAttributes(['id' => 'from-input'])
                            ->options(Client::from())
                            ->default(Client::FROM_VK)
                            ->required()
                            ->customWrapperAttributes([
                                'class' => 'hidden',
                            ]),
                        Textarea::make('Комментарий', 'client_comment')
                            ->customAttributes(['id' => 'comment-input'])
                            ->customWrapperAttributes([
                                'class' => 'hidden',
                            ]),
                    ]),

                    Box::make([
                        Hidden::make('ID клиента', 'client_id')
                            ->customAttributes(['id' => 'client-id']),
                        Select::make('Что в заказе', 'contents')
                            ->options(OrderContent::contents())
                            ->multiple()
                            ->required(),
                        Date::make('Дата выдачи', 'date_issue')
                            ->withTime()
                            ->required(),
                        Textarea::make('Описание заказа', 'description')->required(),
                        Number::make('Сумма заказа', 'amount_total')->required(),
                        Number::make('Оплаченная сумма', 'amount_payed')->required(),
                    ]),
                ])
                ->fill()
                ->submit(__('moonshine::ui.save'), ['class' => 'btn-primary btn-lg'])
                ->async(),
        ];
    }

    /**
     * @return list<ComponentContract>
     * @throws Throwable
     */
    protected function bottomLayer(): array
    {
        return [
            ...parent::bottomLayer()
        ];
    }

    protected function onLoad(): void
    {
        $this->getAssetManager()
            ->append(Js::make('/assets/js/main.js'));
    }

}
