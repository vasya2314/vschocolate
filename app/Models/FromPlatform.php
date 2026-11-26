<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class FromPlatform extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'from_platforms';

    protected $fillable = [
        'name',
    ];

    #[Scope]
    protected function findByName(Builder $query, string $name, bool $withTrashed = false, ?string $excludeId = null): void
    {
        $query->withTrashed($withTrashed)->where('name', $name);

        if ($excludeId) $query->where('id', '!=', $excludeId);
    }

    public function clients(): HasMany
    {
        return $this->hasMany(Client::class);
    }

    /**
     * Заказы клиентов, пришедших с этой площадки.
     * Учитываем и удалённых клиентов: их заказы и выручка остаются в статистике.
     */
    public function orders(): HasManyThrough
    {
        return $this->hasManyThrough(Order::class, Client::class)->withTrashedParents();
    }
}
