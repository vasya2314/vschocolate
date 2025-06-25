<?php

declare(strict_types=1);

namespace App\MoonShine\Resources;

use App\Rules\UniquePhone;
use App\Models\Client;
use MoonShine\Laravel\Enums\Action;
use MoonShine\Laravel\Resources\ModelResource;
use MoonShine\Support\ListOf;
use MoonShine\UI\Components\Layout\Box;
use MoonShine\UI\Fields\Date;
use MoonShine\UI\Fields\Hidden;
use MoonShine\UI\Fields\ID;
use MoonShine\Contracts\UI\FieldContract;
use MoonShine\Contracts\UI\ComponentContract;
use MoonShine\UI\Fields\Phone;
use MoonShine\UI\Fields\Select;
use MoonShine\UI\Fields\Text;
use MoonShine\UI\Fields\Textarea;

/**
 * @extends ModelResource<Client>
 */
class ClientResource extends ModelResource
{
    protected string $model = Client::class;

    protected string $title = 'Клиенты';
    protected int $itemsPerPage = 50;
    protected bool $columnSelection = true;
    protected bool $editInModal = true;

    protected function topButtons(): ListOf
    {
        return parent::topButtons()->only();
    }

    protected function activeActions(): ListOf
    {
        return parent::activeActions()
            ->except(Action::VIEW, Action::DELETE);
    }

    /**
     * @return list<FieldContract>
     */
    protected function indexFields(): iterable
    {
        return [
            ID::make()->customWrapperAttributes(['style' => 'position: sticky; left: 0; z-index: 10; background: inherit;']),
            Text::make('Имя', 'name'),
            Text::make('Телефон', 'phone_view')->changePreview(function ($value, $ctx) {
                return '<a href="tel:' . $value .'">' . $value . '</a>';
            }),
            Text::make('Откуда', 'from_view'),
            Textarea::make('Комментарий', 'short_comment'),
            Date::make('Дата создания', 'created_at')->format('d.m.Y H:i')->sortable(),
        ];
    }

    /**
     * @return list<ComponentContract|FieldContract>
     */
    protected function formFields(): iterable
    {
        return [
            Box::make([
                Hidden::make('ID', 'id')
                    ->required(),
                Text::make('Имя', 'name')
                    ->required(),
                Phone::make('Телефон клиента', 'phone')
                    ->required()
                    ->mask('+7 (999) 999-99-99'),
                Select::make('Откуда', 'from')
                    ->options(Client::from())
                    ->required(),
                Textarea::make('Комментарий', 'comment'),
            ])
        ];
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
     * @param Client $item
     *
     * @return array<string, string[]|string>
     * @see https://laravel.com/docs/validation#available-validation-rules
     */
    protected function rules(mixed $item): array
    {
        if($item)
        {
            return [
                'name' => 'sometimes|required|string',
                'phone' => ['sometimes','required', new UniquePhone('clients', 'phone', $item->id), 'phone:RU'],
                'from' => 'sometimes|required|in:' . implode(',', Client::getAllFromLabels()),
                'comment' => 'required|string',
            ];
        }

        return [];
    }

    public function validationMessages(): array
    {
        return [
            'phone.phone' => 'Значение имеет неверный формат',
        ];
    }

    protected function filters(): iterable
    {
        return [
            Select::make('Откуда клиент', 'from_client')
                ->options(Client::from())
                ->multiple()
                ->onApply(function($item, $value, $field) {
                    if(!empty($value))
                    {
                        $item->whereIn('from', $value);
                    }

                    return $item;
                }),

            Text::make('Телефон', 'phone')
                ->mask('+7 (999) 999-99-99')
                ->onApply(function($item, $value, $field) {
                    if($value) $item->where('phone', makePhoneNormalized($value));

                    return $item;
                }),

        ];
    }

}
