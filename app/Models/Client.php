<?php

namespace App\Models;

use App\Traits\Filterable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Propaganistas\LaravelPhone\PhoneNumber;

class Client extends Model
{
    use HasFactory, SoftDeletes, Filterable;

    protected $table = 'clients';

    protected $fillable = [
        'name',
        'phone',
        'from_platform_id',
        'comment',
    ];

    #[Scope]
    protected function findByPhone(Builder $query, string $phone, bool $withTrashed = false, ?string $excludeId = null): void
    {
        $query->withTrashed($withTrashed)->where('phone', $phone);

        if ($excludeId) $query->where('id', '!=', $excludeId);
    }

    public function getPhoneViewAttribute(): null|string
    {
        if(!$this->phone) return null;

        $phone = new PhoneNumber($this->phone, 'RU');

        return $phone->formatNational();
    }

    public function getShortCommentAttribute(): null|string
    {
        return $this->comment ? Str::limit($this->comment, 100, '...') : null;
    }

    public function getCreatedAtViewAttribute(): null|string
    {
        return $this->created_at->format('d.m.Y H:i:s');
    }

    public function fromPlatform(): BelongsTo
    {
        return $this->belongsTo(FromPlatform::class);
    }
}
