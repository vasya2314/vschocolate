<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    public const NAMES = [
        'Клубника в шоколаде',
        'Шоколадный букет',
        'Набор конфет ручной работы',
        'Шоколадная плитка с орехами',
        'Торт «Три шоколада»',
        'Макарони, 12 шт.',
        'Капкейки, 6 шт.',
        'Шоколадные фигурки',
        'Подарочный бокс',
        'Трюфели ассорти',
    ];

    public function definition(): array
    {
        return [
            'name' => mb_substr('Шоколад «' . fake()->unique()->word() . '»', 0, 50),
        ];
    }
}
