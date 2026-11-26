<?php

use App\Models\Order;

beforeEach(function () {
    $this->actingAsUser();
    $this->travelTo('2026-11-18 12:00:00');
});

test('главная открывается за текущий месяц со всеми блоками', function () {
    Order::factory()->count(3)->create();

    $this->get(route('dashboard.index'))
        ->assertOk()
        ->assertViewHas('month', '2026-11')
        ->assertSeeInOrder([
            'Новых клиентов', 'Средний чек', 'Доля отмен',
            'Заказы по дням', 'Выручка по месяцам, 2026',
            'Источники клиентов', 'Популярные товары',
            'Повторные клиенты', 'Нагрузка по дням недели',
            'Долг клиентов',
        ]);
});

test('выбранный и пустой месяц, неверный параметр', function () {
    $this->get(route('dashboard.index', ['month' => '2020-01']))
        ->assertOk()
        ->assertViewHas('month', '2020-01');

    $this->get(route('dashboard.index', ['month' => '2026-13']))
        ->assertOk()
        ->assertViewHas('month', '2026-11');
});
