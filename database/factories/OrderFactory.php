<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    private const DESCRIPTIONS = [
        'На день рождения, надпись «С днём рождения!»',
        'Без орехов, аллергия',
        'Упаковать в крафт, ленту красную',
        'Доставка курьером к 18:00',
        'Самовывоз',
        'Корпоративный заказ, нужен чек',
        'Белый шоколад вместо молочного',
        'Подарок на годовщину',
        'Добавить открытку',
    ];

    public function definition(): array
    {
        return [
            'date_issue' => fake()->dateTimeBetween('-1 month', '+1 month'),
            'client_id' => Client::factory(),
            'description' => fake()->randomElement(self::DESCRIPTIONS),
            'amount_total' => fake()->numberBetween(15, 150) * 10000,
            'status' => fn (array $attrs) => $this->statusFor(Carbon::parse($attrs['date_issue'])),
            'amount_payed' => fn (array $attrs) => $attrs['status'] === Order::STATUS_COMPLETE
                ? $attrs['amount_total']
                : fake()->randomElement([0, intdiv($attrs['amount_total'], 2), $attrs['amount_total']]),
        ];
    }

    /** Дата выдачи в конкретный день, время — в рабочие часы. */
    public function onDate(CarbonInterface $date): static
    {
        return $this->state(fn () => [
            'date_issue' => $date->copy()->setTime(fake()->numberBetween(10, 20), fake()->randomElement([0, 30])),
        ]);
    }

    public function status(string $status): static
    {
        return $this->state(fn () => ['status' => $status]);
    }

    /**
     * Привязать 1–3 товара. Если коллекция не передана — берутся существующие товары,
     * а при их отсутствии создаются новые.
     */
    public function withProducts(?Collection $products = null): static
    {
        return $this->afterCreating(function (Order $order) use ($products) {
            $pool = $products ?? Product::query()->inRandomOrder()->limit(10)->get();

            if ($pool->isEmpty()) {
                $pool = Product::factory()->count(3)->create();
            }

            $order->products()->sync($pool->random(min($pool->count(), fake()->numberBetween(1, 3)))->pluck('id'));
        });
    }

    private function statusFor(Carbon $date): string
    {
        if ($date->lt(Carbon::today())) {
            return fake()->randomElement(array_merge(
                array_fill(0, 85, Order::STATUS_COMPLETE),
                array_fill(0, 8, Order::STATUS_CANCELED),
                array_fill(0, 7, Order::STATUS_PROGRESS),
            ));
        }

        return fake()->randomElement(array_merge(
            array_fill(0, 60, Order::STATUS_ACCEPT),
            array_fill(0, 35, Order::STATUS_PROGRESS),
            array_fill(0, 5, Order::STATUS_CANCELED),
        ));
    }
}
