<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderContent extends Model
{
    use HasFactory;

    protected $guarded = false;
    protected $table = 'order_contents';

    public $timestamps = false;

    public const PRODUCT_STRAWBERRY = 'STRAWBERRY'; // Клубника
    public const PRODUCT_BOUQUET = 'BOUQUET'; // Букеты
    public const PRODUCT_CANDIES = 'CANDIES'; // Конфеты

    private const PRODUCTS = [
        self::PRODUCT_STRAWBERRY => 'Клубника',
        self::PRODUCT_BOUQUET => 'Букет',
        self::PRODUCT_CANDIES => 'Конфеты',
    ];

    public static function getAllContents(): array
    {
        return array_keys(self::PRODUCTS);
    }

    public static function contents(): array
    {
        return self::PRODUCTS;
    }

    public function getContentViewAttribute(): string
    {
        return self::PRODUCTS[$this->key];
    }

}
