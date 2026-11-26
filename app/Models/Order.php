<?php

namespace App\Models;

use App\Traits\Filterable;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, SoftDeletes, Filterable;

    const STATUS_ACCEPT = 'ACCEPT';
    const STATUS_COMPLETE = 'COMPLETE';
    const STATUS_PROGRESS = 'PROGRESS';
    const STATUS_CANCELED = 'CANCELED';

    private const STATUSES = [
        self::STATUS_ACCEPT => 'Принят',
        self::STATUS_COMPLETE => 'Выдан',
        self::STATUS_PROGRESS => 'Выполнен',
        self::STATUS_CANCELED => 'Отменен',
    ];

    /** Цвета бейджа статуса (Tailwind) */
    private const STATUS_CLASSES = [
        self::STATUS_ACCEPT => 'text-orange-700 bg-orange-100',
        self::STATUS_PROGRESS => 'text-gray-700 bg-gray-100',
        self::STATUS_COMPLETE => 'text-green-700 bg-green-100',
        self::STATUS_CANCELED => 'text-red-700 bg-red-100',
    ];

    protected $table = 'orders';

    protected $fillable = [
        'date_issue',
        'client_id',
        'description',
        'amount_total',
        'amount_payed',
        'status'
    ];

    /** Суммы хранятся в целых копейках */
    protected $casts = [
        'amount_total' => 'integer',
        'amount_payed' => 'integer',
    ];

    protected $attributes = [
        'status' => self::STATUS_ACCEPT,
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

    public function getStatusClassesAttribute(): string
    {
        return self::STATUS_CLASSES[$this->status] ?? '';
    }

    /** Недоплата по заказу в копейках */
    public function getDebtAttribute(): int
    {
        return max(0, $this->amount_total - $this->amount_payed);
    }

    public function getShortDescriptionAttribute(): null|string
    {
        return $this->description ? Str::limit($this->description, 150, '...') : null;
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class);
    }

}
