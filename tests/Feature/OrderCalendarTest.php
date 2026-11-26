<?php

use App\Models\Client;
use App\Models\Order;
use App\Models\Product;

beforeEach(function () {
    $this->actingAsUser();
    $this->travelTo('2026-11-18 12:00:00'); // среда
});

function calendarOrder(string $dateIssue, array $attributes = []): Order
{
    return Order::factory()->for(Client::factory())->create(['date_issue' => $dateIssue] + $attributes);
}

/** Дни календаря из view, по ключу Y-m-d */
function calendarDays($test, string $url): array
{
    $days = null;
    $test->get($url)->assertOk()->assertViewHas('days', function ($value) use (&$days) {
        $days = $value;

        return true;
    });

    return collect($days)->keyBy('date')->all();
}

test('месяц: номер заказа, товары, время и ссылка на заказ', function () {
    $order = calendarOrder('2026-11-20 15:30:00');
    $order->products()->attach(Product::factory()->create(['name' => 'Трюфели ассорти']));

    $this->get(route('order.calendar'))
        ->assertOk()
        ->assertSee('Ноябрь 2026')
        ->assertSee('№' . $order->id)
        ->assertSee('Трюфели ассорти')
        ->assertSee('15:30')
        ->assertSee(route('order.edit', $order->id), false);
});

test('сетка месяца — полные недели с понедельника по воскресенье', function () {
    $days = calendarDays($this, route('order.calendar'));

    expect(array_key_first($days))->toBe('2026-10-26')
        ->and(array_key_last($days))->toBe('2026-12-06')
        ->and($days['2026-10-26']['in_period'])->toBeFalse()
        ->and($days['2026-11-18']['is_today'])->toBeTrue();
});

test('сумма дня без отменённых заказов', function () {
    calendarOrder('2026-11-20 10:00:00', ['amount_total' => 300_000, 'status' => Order::STATUS_ACCEPT]);
    calendarOrder('2026-11-20 11:00:00', ['amount_total' => 500_000, 'status' => Order::STATUS_CANCELED]);

    $days = calendarDays($this, route('order.calendar'));

    expect($days['2026-11-20']['orders'])->toHaveCount(2)
        ->and($days['2026-11-20']['sum'])->toBe(rub('3 000 ₽'));
});

test('неделя показывает только текущую неделю', function () {
    $inWeek = calendarOrder('2026-11-22 10:00:00');
    calendarOrder('2026-11-23 10:00:00');

    $days = calendarDays($this, route('order.calendar', ['view' => 'week']));

    expect(array_keys($days))->toHaveCount(7)
        ->and(array_key_first($days))->toBe('2026-11-16')
        ->and(array_key_last($days))->toBe('2026-11-22')
        ->and($days['2026-11-22']['orders']->pluck('id')->all())->toBe([$inWeek->id]);
});

test('переход на другой месяц и неверная дата', function () {
    $this->get(route('order.calendar', ['date' => '2026-02-10']))
        ->assertOk()
        ->assertSee('Февраль 2026')
        ->assertViewHas('prev', '2026-01-10')
        ->assertViewHas('next', '2026-03-10');

    $this->get(route('order.calendar', ['date' => 'garbage']))
        ->assertOk()
        ->assertSee('Ноябрь 2026');
});

test('заказы удалённого клиента остаются в календаре', function () {
    $order = calendarOrder('2026-11-20 10:00:00');
    $order->client->delete();

    $this->get(route('order.calendar'))->assertOk()->assertSee('№' . $order->id);
});
