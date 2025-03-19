<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Propaganistas\LaravelPhone\PhoneNumber;
use Illuminate\Support\Str;

class Client extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'clients';

    const FROM_VK = 'VK';
    const FROM_AVITO = 'AVITO';

    private const FROM = [
        self::FROM_VK => 'ВК',
        self::FROM_AVITO => 'АВИТО',
    ];

    public static function getAllFromLabels(): array
    {
        return array_keys(self::FROM);
    }

    public static function from(): array
    {
        return self::FROM;
    }

    public function getPhoneViewAttribute(): null|string
    {
        if(!$this->phone) return null;

        $phone = new PhoneNumber($this->phone, 'RU');

        return $phone->formatNational();
    }

    public function getFromViewAttribute(): null|string
    {
        return self::FROM[$this->from];
    }

    public function getShortCommentAttribute(): null|string
    {
        return $this->comment ? Str::limit($this->comment, 100, '...') : null;
    }

    public function getCreatedAtViewAttribute(): null|string
    {
        return $this->created_at->format('d.m.Y H:i:s');
    }

    public function setPhoneAttribute($phoneCandidate): void
    {
        $this->attributes['phone'] = makePhoneNormalized($phoneCandidate);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

}
