<?php

use App\Models\Client;
use App\Models\FromPlatform;
use App\Models\Order;
use App\View\Components\Alert;

beforeEach(function () {
    $this->actingAsUser();
    $this->platform = FromPlatform::factory()->create();
});

test('список клиентов', function () {
    $client = Client::factory()->create();

    $this->get(route('client.index'))->assertOk()->assertSee($client->name);
});

test('создание клиента с нормализацией телефона', function () {
    $this->post(route('client.store'), [
        'name' => 'Ольга',
        'phone' => '+7 (912) 345-67-89',
        'from_platform_id' => $this->platform->id,
    ])->assertRedirect(route('client.index'))->assertSessionHas(Alert::TYPE_SUCCESS);

    $this->assertDatabaseHas('clients', ['name' => 'Ольга', 'phone' => '+79123456789']);
});

test('дубль и неверный телефон отклоняются', function () {
    Client::factory()->create(['phone' => '+79123456789']);

    $this->post(route('client.store'), ['name' => 'Дубль', 'phone' => '+79123456789', 'from_platform_id' => $this->platform->id])
        ->assertSessionHasErrors('phone');
    $this->post(route('client.store'), ['name' => 'Неверный', 'phone' => '123', 'from_platform_id' => $this->platform->id])
        ->assertSessionHasErrors('phone');

    expect(Client::count())->toBe(1);
});

test('редактирование клиента', function () {
    $client = Client::factory()->create();

    $this->patch(route('client.update', $client), [
        'name' => 'Новое имя',
        'phone' => $client->phone,
        'from_platform_id' => $this->platform->id,
        'comment' => 'Постоянный клиент',
    ])->assertRedirect(route('client.edit', $client));

    expect($client->refresh())
        ->name->toBe('Новое имя')
        ->comment->toBe('Постоянный клиент');
});

test('удаление мягкое, клиента можно восстановить', function () {
    $client = Client::factory()->create();

    $this->delete(route('client.delete', $client));
    $this->assertSoftDeleted($client);

    $this->post(route('client.restore', $client->id));
    $this->assertNotSoftDeleted($client);
});

test('поиск по телефону: клиент или 404', function () {
    $client = Client::factory()->create(['phone' => '+79123456789']);

    $this->postJson(route('client.getClientByPhone'), ['phone' => '+79123456789'])
        ->assertOk()
        ->assertJsonPath('id', $client->id);

    $this->postJson(route('client.getClientByPhone'), ['phone' => '+79990000000'])
        ->assertNotFound();
});

test('карточка: статистика без отменённых заказов', function () {
    $client = Client::factory()->create();
    $make = fn (string $status, int $total, int $payed, string $date) => Order::factory()->for($client)->create([
        'status' => $status, 'amount_total' => $total, 'amount_payed' => $payed, 'date_issue' => $date,
    ]);
    $make(Order::STATUS_COMPLETE, 300_000, 300_000, '2026-10-01 10:00:00');
    $make(Order::STATUS_COMPLETE, 500_000, 200_000, '2026-10-15 10:00:00'); // долг 3 000 ₽
    $make(Order::STATUS_ACCEPT, 100_000, 0, '2026-11-20 10:00:00');         // долг 1 000 ₽
    $make(Order::STATUS_CANCELED, 900_000, 0, '2026-11-25 10:00:00');       // не учитывается
    Order::factory()->create(['amount_total' => 999_999]);                   // чужой заказ

    $response = $this->get(route('client.edit', $client))
        ->assertOk()
        ->assertViewHas('orders', fn ($orders) => $orders->total() === 4)
        ->assertSee(rub('9 000 ₽'))
        ->assertSee(rub('4 000 ₽'));

    expect($response->viewData('stats'))
        ->total_count->toBe(4)
        ->active_count->toBe(3)
        ->canceled_count->toBe(1)
        ->amount_total->toBe(900_000)
        ->amount_payed->toBe(500_000)
        ->debt->toBe(400_000)
        ->average->toBe(300_000)
        ->and($response->viewData('stats')['first_date']->format('Y-m-d'))->toBe('2026-10-01')
        ->and($response->viewData('stats')['last_date']->format('Y-m-d'))->toBe('2026-11-25');
});

test('карточка клиента без заказов', function () {
    $client = Client::factory()->create();

    $response = $this->get(route('client.edit', $client))
        ->assertOk()
        ->assertSee('У клиента пока нет заказов');

    expect($response->viewData('stats'))
        ->average->toBe(0)
        ->first_date->toBeNull();
});
