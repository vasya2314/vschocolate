<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'orders';

    const STATUS_ACCEPT = 'ACCEPT';
    const STATUS_COMPLETE = 'COMPLETE';
    const STATUS_PROGRESS = 'PROGRESS';
    const STATUS_CANCELED = 'CANCELED';

    private const STATUSES = [
        self::STATUS_ACCEPT => 'Принят',
        self::STATUS_COMPLETE => 'Выполнен',
        self::STATUS_PROGRESS => 'В процессе выполнения',
        self::STATUS_CANCELED => 'Отменен',
    ];

    public static function getAllStatuses(): array
    {
        return array_keys(self::STATUSES);
    }

    public static function statuses(): array
    {
        return self::STATUSES;
    }

    public function getDateIssueViewAttribute(): null|string
    {
        return Carbon::createFromFormat('Y-m-d H:i:s', $this->date_issue)->format('d.m.Y H:i');
    }

    public function getAmountTotalViewAttribute(): null|string
    {
        return kopToRubView($this->amount_total);
    }

    public function getAmountPayedViewAttribute(): null|string
    {
        return kopToRubView($this->amount_payed);
    }

    public function getCreatedAtViewAttribute(): null|string
    {
        return $this->created_at->format('d.m.Y H:i:s');
    }

    public function getStatusViewAttribute(): null|string
    {
        return self::STATUSES[$this->status];
    }

    public function getShortDescriptionAttribute(): null|string
    {
        return $this->description ? Str::limit($this->description, 150, '...') : null;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function contents(): HasMany
    {
        return $this->hasMany(OrderContent::class);
    }
}
