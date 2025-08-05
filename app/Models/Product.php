<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'name',
    ];

    #[Scope]
    protected function findByName(Builder $query, string $name, bool $withTrashed = false, ?string $excludeId = null): void
    {
        $query->withTrashed($withTrashed)->where('name', $name);

        if ($excludeId) $query->where('id', '!=', $excludeId);
    }

    public function orders(): BelongsToMany
    {
        return $this->belongsToMany(Order::class);
    }
}
