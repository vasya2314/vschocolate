<?php

namespace App\Http\Filters\Models;

use App\Http\Filters\AbstractFilter;
use Illuminate\Database\Eloquent\Builder;

class ClientFilter extends AbstractFilter
{
    public const PHONE = 'phone';
    public const FROM_PLATFORM_ID = 'from_platform_id';

    protected function getCallbacks(): array
    {
        return [
            self::PHONE => [$this, 'phone'],
            self::FROM_PLATFORM_ID => [$this, 'fromPlatformId'],
        ];
    }

    public function phone(Builder $builder, string $value): void
    {
        $builder->where('phone', 'like', "%{$value}%");
    }

    public function fromPlatformId(Builder $builder, array $value): void
    {
        $builder->whereIn('from_platform_id', $value);
    }
}
