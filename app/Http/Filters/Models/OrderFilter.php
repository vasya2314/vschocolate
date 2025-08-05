<?php

namespace App\Http\Filters\Models;

use App\Http\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderFilter extends AbstractFilter
{
    public const STATUSES = 'statuses';
    public const PRODUCTS = 'products';

    protected function getCallbacks(): array
    {
        return [
            self::STATUSES => [$this, 'statuses'],
            self::PRODUCTS => [$this, 'products'],
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
}
