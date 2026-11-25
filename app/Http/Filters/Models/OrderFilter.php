<?php

namespace App\Http\Filters\Models;

use App\Http\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends AbstractFilter
{
    public const STATUSES = 'statuses';
    public const PRODUCTS = 'products';
    public const DATE_FROM = 'date_from';
    public const DATE_TO = 'date_to';

    protected function getCallbacks(): array
    {
        return [
            self::STATUSES => [$this, 'statuses'],
            self::PRODUCTS => [$this, 'products'],
            self::DATE_FROM => [$this, 'dateFrom'],
            self::DATE_TO => [$this, 'dateTo'],
        ];
    }

    public function statuses(Builder $builder, array $value): void
    {
        $builder->whereIn('status', $value);
    }

    public function products(Builder $builder, array $value): void
    {
        $builder->whereHas('products', function (Builder $builder) use ($value) {
            $builder->whereIn('products.id', $value);
        });
    }

    public function dateFrom(Builder $builder, string $value): void
    {
        $builder->whereDate('date_issue', '>=', $value);
    }

    public function dateTo(Builder $builder, string $value): void
    {
        $builder->whereDate('date_issue', '<=', $value);
    }
}
