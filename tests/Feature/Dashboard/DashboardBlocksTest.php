<?php

use App\Models\Client;
use App\Models\FromPlatform;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;

beforeEach(function () {
    $this->travelTo('2026-11-18 12:00:00');
    $this->period = ['from' => Carbon::parse('2026-11-01'), 'to' => Carbon::parse('2026-11-30')->endOfDay()];
});

function dashOrder(string $status, int $total, string $date, array $attributes = []): Order
{
    return Order::factory()->create(array_merge([
        'status' => $status,
        'amount_total' => $total,
        'amount_payed' => $total,
        'date_issue' => $date,
    ], $attributes));
}

describe('плашки', function () {
    beforeEach(function () {
        // Ноябрь: 4 активных (2 выдано на 8 000 ₽) + 1 отменённый
        dashOrder(Order::STATUS_COMPLETE, 300_000, '2026-11-02 10:00:00');
        dashOrder(Order::STATUS_COMPLETE, 500_000, '2026-11-03 10:00:00');
        dashOrder(Order::STATUS_ACCEPT, 100_000, '2026-11-20 10:00:00');
        dashOrder(Order::STATUS_PROGRESS, 100_000, '2026-11-21 10:00:00');
        dashOrder(Order::STATUS_CANCELED, 900_000, '2026-11-22 10:00:00');
        // Октябрь: 2 активных, 1 выдан на 4 000 ₽, отмен нет
        dashOrder(Order::STATUS_COMPLETE, 400_000, '2026-10-05 10:00:00');
        dashOrder(Order::STATUS_ACCEPT, 400_000, '2026-10-06 10:00:00');

        $this->items = collect(composeBlock('stats', $this->period)['items'])->keyBy('name');
    });

    test('значения за месяц', function () {
        expect($this->items['Выдано заказов на сумму']['value'])->toBe('8 000 ₽')
            ->and($this->items['Заказов к выдаче']['value'])->toBe(4)        // отменённый не считается
            ->and($this->items['Средний чек']['value'])->toBe('2 500 ₽')      // 10 000 ₽ / 4
            ->and($this->items['Заказов в ожидании']['value'])->toBe(2)
            ->and($this->items['Доля отмен']['value'])->toBe('20% (1 из 5)');
    });

    test('изменение к прошлому месяцу', function () {
        expect($this->items['Выдано заказов на сумму']['delta'])->toMatchArray(['text' => '▲ 100%', 'tone' => 'good'])
            ->and($this->items['Заказов к выдаче']['delta'])->toMatchArray(['text' => '▲ 100%', 'tone' => 'good'])
            ->and($this->items['Средний чек']['delta'])->toMatchArray(['text' => '▼ 38%', 'tone' => 'bad'])
            ->and($this->items['Заказов в ожидании']['delta'])->toBeNull();
    });

    test('доля отмен: прошлое значение, рост отмен — плохо', function () {
        expect($this->items['Доля отмен']['delta'])->toMatchArray([
            'text' => '▲ было 0%',
            'suffix' => 'в прошлом месяце',
            'tone' => 'bad',
            'title' => 'За октябрь 2026: 0% (изменение: +20 п.п.)',
        ]);
    });
});

test('плашки без данных за прошлый месяц показывают прочерк', function () {
    dashOrder(Order::STATUS_COMPLETE, 300_000, '2026-11-02 10:00:00');

    $items = collect(composeBlock('stats', $this->period)['items'])->keyBy('name');

    expect($items['Выдано заказов на сумму']['delta']['text'])->toBe('—')
        ->and($items['Доля отмен']['delta']['text'])->toBe('—');
});

test('график по дням: ссылка на заказы дня и количество', function () {
    dashOrder(Order::STATUS_ACCEPT, 100_000, '2026-11-20 10:00:00');
    dashOrder(Order::STATUS_CANCELED, 100_000, '2026-11-20 11:00:00');

    $days = collect(composeBlock('orders-chart', $this->period)['days'])->keyBy('day');

    expect($days)->toHaveCount(30)
        ->and($days[20]['count'])->toBe(1)
        ->and($days[20]['url'])->toBe(route('order.index', ['date_from' => '2026-11-20', 'date_to' => '2026-11-20']))
        ->and($days[18]['is_today'])->toBeTrue();
});

test('выручка по месяцам за год без отменённых', function () {
    dashOrder(Order::STATUS_COMPLETE, 800_000, '2026-10-10 10:00:00');
    dashOrder(Order::STATUS_ACCEPT, 1_000_000, '2026-11-10 10:00:00');
    dashOrder(Order::STATUS_CANCELED, 5_000_000, '2026-11-11 10:00:00');
    dashOrder(Order::STATUS_COMPLETE, 7_000_000, '2025-11-10 10:00:00'); // прошлый год

    $data = composeBlock('year-revenue', $this->period);
    $months = collect($data['months']);

    expect($data['year'])->toBe(2026)
        ->and($data['yearSum'])->toBe(1_800_000)
        ->and($months)->toHaveCount(12)
        ->and($months[9])->toMatchArray(['sum' => 800_000, 'count' => 1, 'is_selected' => false])
        ->and($months[10])->toMatchArray(['sum' => 1_000_000, 'count' => 1, 'is_selected' => true])
        ->and($months[10]['url'])->toBe(route('dashboard.index', ['month' => '2026-11']))
        ->and($data['scaleMax'])->toBeGreaterThanOrEqual(1_000_000);
});

test('источники клиентов: новые клиенты и выручка по площадке', function () {
    $vk = FromPlatform::factory()->create(['name' => 'VK']);
    $avito = FromPlatform::factory()->create(['name' => 'Авито']);
    FromPlatform::factory()->create(['name' => 'Пустая']);

    $vkClient = Client::factory()->for($vk)->create(['created_at' => '2026-11-05']);
    $vkOld = Client::factory()->for($vk)->create(['created_at' => '2026-09-01']);
    $avitoClient = Client::factory()->for($avito)->create(['created_at' => '2026-11-06']);

    dashOrder(Order::STATUS_COMPLETE, 300_000, '2026-11-10 10:00:00', ['client_id' => $vkClient->id]);
    dashOrder(Order::STATUS_ACCEPT, 300_000, '2026-11-11 10:00:00', ['client_id' => $vkOld->id]);
    dashOrder(Order::STATUS_CANCELED, 900_000, '2026-11-12 10:00:00', ['client_id' => $vkOld->id]);
    dashOrder(Order::STATUS_COMPLETE, 200_000, '2026-11-13 10:00:00', ['client_id' => $avitoClient->id]);
    dashOrder(Order::STATUS_COMPLETE, 999_000, '2026-10-13 10:00:00', ['client_id' => $avitoClient->id]); // другой месяц
    $vkOld->delete(); // выручка удалённого клиента остаётся

    $data = composeBlock('sources', $this->period);

    expect($data['totalRevenue'])->toBe(800_000)
        ->and($data['rows']->all())->toBe([
            ['name' => 'VK', 'clients' => 1, 'orders' => 2, 'revenue' => 600_000, 'share' => 75],
            ['name' => 'Авито', 'clients' => 1, 'orders' => 1, 'revenue' => 200_000, 'share' => 25],
        ]);
});

test('популярные товары: число заказов без отменённых и удалённых', function () {
    [$truffles, $box, $unused] = Product::factory()->count(3)->sequence(
        ['name' => 'Трюфели'], ['name' => 'Бокс'], ['name' => 'Не продавался'],
    )->create();

    $attach = fn (Order $order, array $products) => $order->products()->attach(collect($products)->pluck('id'));
    $attach(dashOrder(Order::STATUS_COMPLETE, 100_000, '2026-11-02 10:00:00'), [$truffles, $box]);
    $attach(dashOrder(Order::STATUS_ACCEPT, 100_000, '2026-11-03 10:00:00'), [$truffles]);
    $attach(dashOrder(Order::STATUS_CANCELED, 100_000, '2026-11-04 10:00:00'), [$box]);
    $attach(dashOrder(Order::STATUS_COMPLETE, 100_000, '2026-10-04 10:00:00'), [$box]);
    $deleted = dashOrder(Order::STATUS_COMPLETE, 100_000, '2026-11-05 10:00:00');
    $attach($deleted, [$box]);
    $deleted->delete();

    $data = composeBlock('top-products', $this->period);

    expect($data['ordersCount'])->toBe(2)
        ->and($data['rows']->all())->toBe([
            ['name' => 'Трюфели', 'count' => 2, 'share' => 100],
            ['name' => 'Бокс', 'count' => 1, 'share' => 50],
        ]);
});

test('повторные клиенты: доля заказов и топ по сумме', function () {
    $loyal = Client::factory()->create(['name' => 'Постоянная']);
    $newbie = Client::factory()->create(['name' => 'Новенькая']);
    $canceledTwice = Client::factory()->create(['name' => 'Отменяла']);

    dashOrder(Order::STATUS_COMPLETE, 500_000, '2026-09-10 10:00:00', ['client_id' => $loyal->id]);
    dashOrder(Order::STATUS_COMPLETE, 300_000, '2026-11-10 10:00:00', ['client_id' => $loyal->id]);
    dashOrder(Order::STATUS_ACCEPT, 900_000, '2026-11-11 10:00:00', ['client_id' => $newbie->id]);
    dashOrder(Order::STATUS_ACCEPT, 100_000, '2026-11-12 10:00:00', ['client_id' => $canceledTwice->id]);
    dashOrder(Order::STATUS_CANCELED, 100_000, '2026-10-12 10:00:00', ['client_id' => $canceledTwice->id]);

    $data = composeBlock('repeat-clients', $this->period);

    expect($data)
        ->periodOrders->toBe(3)
        ->repeatOrders->toBe(1)          // только «Постоянная»: отменённый заказ не делает клиента повторным
        ->repeatShare->toBe(33)
        ->periodClients->toBe(3)
        ->repeatClients->toBe(1)
        ->and($data['top']->map(fn ($row) => [$row['client']->name, $row['total'], $row['count']])->all())
        ->toBe([
            ['Новенькая', 900_000, 1],
            ['Постоянная', 800_000, 2],
            ['Отменяла', 100_000, 1],
        ]);
});

test('нагрузка по дням недели: среднее на один день с первого заказа', function () {
    // Первый заказ — понедельник 02.11, период 02.11–30.11: по 5 понедельников, по 4 остальных дня
    dashOrder(Order::STATUS_COMPLETE, 100_000, '2026-11-02 10:00:00'); // пн
    dashOrder(Order::STATUS_COMPLETE, 100_000, '2026-11-09 10:00:00'); // пн
    dashOrder(Order::STATUS_COMPLETE, 100_000, '2026-11-14 10:00:00'); // сб
    dashOrder(Order::STATUS_COMPLETE, 100_000, '2026-11-14 12:00:00'); // сб
    dashOrder(Order::STATUS_CANCELED, 100_000, '2026-11-15 10:00:00'); // вс, отменён

    $data = composeBlock('weekday-load', $this->period);
    $days = collect($data['days'])->keyBy('short');

    expect($data['total'])->toBe(4)
        ->and($data['busiest'])->toBe('Суббота')
        ->and($data['periodLabel'])->toBe('02.11.2026 — 30.11.2026')
        ->and($days['Пн'])->toMatchArray(['count' => 2, 'avg' => 2 / 5])
        ->and($days['Сб'])->toMatchArray(['count' => 2, 'avg' => 2 / 4, 'is_weekend' => true])
        ->and($days['Вс']['count'])->toBe(0);
});

test('долг клиентов: по выданным — по клиентам, по невыданным — ожидаемая доплата', function () {
    $anna = Client::factory()->create(['name' => 'Анна']);
    $oleg = Client::factory()->create(['name' => 'Олег']);

    dashOrder(Order::STATUS_COMPLETE, 500_000, '2026-10-01 10:00:00', ['client_id' => $anna->id, 'amount_payed' => 100_000]);
    dashOrder(Order::STATUS_COMPLETE, 300_000, '2026-11-01 10:00:00', ['client_id' => $anna->id, 'amount_payed' => 0]);
    dashOrder(Order::STATUS_COMPLETE, 200_000, '2026-11-02 10:00:00', ['client_id' => $oleg->id, 'amount_payed' => 100_000]);
    dashOrder(Order::STATUS_COMPLETE, 200_000, '2026-11-03 10:00:00', ['client_id' => $oleg->id]);                          // оплачен
    dashOrder(Order::STATUS_ACCEPT, 400_000, '2026-11-25 10:00:00', ['client_id' => $oleg->id, 'amount_payed' => 100_000]);  // ожидается
    dashOrder(Order::STATUS_CANCELED, 900_000, '2026-11-26 10:00:00', ['client_id' => $oleg->id, 'amount_payed' => 0]);       // не долг

    $data = composeBlock('debts');

    expect($data)
        ->totalDebt->toBe(800_000)
        ->debtOrders->toBe(3)
        ->debtorsCount->toBe(2)
        ->expectedDebt->toBe(300_000)
        ->expectedOrders->toBe(1)
        ->hidden->toBe(0)
        ->and($data['rows']->map(fn ($row) => [$row['client']->name, $row['count'], $row['debt'], $row['oldest']->format('Y-m-d')])->all())
        ->toBe([
            ['Анна', 2, 700_000, '2026-10-01'],
            ['Олег', 1, 100_000, '2026-11-02'],
        ]);
});
