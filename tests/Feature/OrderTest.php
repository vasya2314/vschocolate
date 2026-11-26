<?php

use App\Models\Client;
use App\Models\FromPlatform;
use App\Models\Order;
use App\Models\Product;
use App\View\Components\Alert;

beforeEach(function () {
    $this->actingAsUser();
    $this->client = Client::factory()->create();
    $this->product = Product::factory()->create();

    $this->orderData = fn (array $overrides = []) => array_merge([
        'client' => ['id' => $this->client->id],
        'status' => Order::STATUS_ACCEPT,
        'date_issue' => '2026-11-27T12:00',
        'description' => 'Без орехов',
        'amount_total' => '12 345',
        'amount_payed' => '5 000',
        'product_ids' => [$this->product->id],
    ], $overrides);
});

function orderIds($orders): array
{
    return collect($orders->items())->pluck('id')->all();
}

describe('список и фильтры', function () {
    test('показывает заказы с клиентом и товарами', function () {
        $order = Order::factory()->for($this->client)->create();
        $order->products()->attach($this->product);

        $this->get(route('order.index'))
            ->assertOk()
            ->assertSee($this->client->name)
            ->assertSee($this->product->name)
            ->assertSee(route('client.edit', $this->client->id), false);
    });

    test('фильтрует по статусу и товару', function () {
        $accepted = Order::factory()->for($this->client)->create(['status' => Order::STATUS_ACCEPT]);
        $accepted->products()->attach($this->product);
        $canceled = Order::factory()->for($this->client)->create(['status' => Order::STATUS_CANCELED]);

        $this->get(route('order.index', ['statuses' => [Order::STATUS_ACCEPT]]))
            ->assertViewHas('orders', fn ($orders) => orderIds($orders) === [$accepted->id]);

        $this->get(route('order.index', ['products' => [$this->product->id]]))
            ->assertViewHas('orders', fn ($orders) => orderIds($orders) === [$accepted->id]);

        $this->get(route('order.index', ['statuses' => [Order::STATUS_CANCELED]]))
            ->assertViewHas('orders', fn ($orders) => orderIds($orders) === [$canceled->id]);
    });

    test('фильтрует по дате выдачи', function () {
        $inside = Order::factory()->for($this->client)->create(['date_issue' => '2026-11-26 18:00:00']);
        Order::factory()->for($this->client)->create(['date_issue' => '2026-11-25 10:00:00']);
        Order::factory()->for($this->client)->create(['date_issue' => '2026-11-27 09:00:00']);

        $this->get(route('order.index', ['date_from' => '2026-11-26', 'date_to' => '2026-11-26']))
            ->assertOk()
            ->assertViewHas('orders', fn ($orders) => orderIds($orders) === [$inside->id]);
    });

    test('отклоняет «по» раньше «с»', function () {
        $this->get(route('order.index', ['date_from' => '2026-11-26', 'date_to' => '2026-11-01']))
            ->assertSessionHasErrors('date_to');
    });

    test('отклоняет неизвестный статус', function () {
        $this->get(route('order.index', ['statuses' => ['HACK']]))
            ->assertSessionHasErrors('statuses');
    });
});

test('сортировка: «Выполнен», затем «Принят» — по возрастанию даты; остальные — по убыванию; удалённые в конце', function () {
    $make = fn (string $status, string $date) => Order::factory()->for($this->client)->create(['status' => $status, 'date_issue' => $date]);

    $completeOld = $make(Order::STATUS_COMPLETE, '2026-10-01 10:00:00');
    $acceptLate = $make(Order::STATUS_ACCEPT, '2026-11-30 10:00:00');
    $progressLate = $make(Order::STATUS_PROGRESS, '2026-11-29 10:00:00');
    $canceledNew = $make(Order::STATUS_CANCELED, '2026-11-15 10:00:00');
    $acceptEarly = $make(Order::STATUS_ACCEPT, '2026-11-20 10:00:00');
    $completeNew = $make(Order::STATUS_COMPLETE, '2026-11-10 10:00:00');
    $progressEarly = $make(Order::STATUS_PROGRESS, '2026-11-21 10:00:00');
    $deletedAccept = $make(Order::STATUS_ACCEPT, '2026-11-01 10:00:00');
    $deletedAccept->delete();

    $this->get(route('order.index'))->assertViewHas('orders', fn ($orders) => orderIds($orders) === [
        $progressEarly->id, $progressLate->id,  // «Выполнен» — ближайшие к выдаче сверху
        $acceptEarly->id, $acceptLate->id,      // «Принят»
        $canceledNew->id, $completeNew->id, $completeOld->id, // остальные — новые сверху
        $deletedAccept->id,                     // удалённые — в самом конце
    ]);
});

describe('создание', function () {
    test('для существующего клиента, суммы в копейках', function () {
        $this->post(route('order.store'), ($this->orderData)())
            ->assertRedirect(route('order.index'))
            ->assertSessionHas(Alert::TYPE_SUCCESS);

        $order = Order::with('products')->sole();

        expect($order->client_id)->toBe($this->client->id)
            ->and($order->amount_total)->toBe(1_234_500)
            ->and($order->amount_payed)->toBe(500_000)
            ->and($order->date_issue)->toBe('2026-11-27 12:00:00')
            ->and($order->products->pluck('id')->all())->toBe([$this->product->id]);
    });

    test('вместе с новым клиентом', function () {
        $platform = FromPlatform::factory()->create();

        $this->post(route('order.store'), ($this->orderData)([
            'client' => ['name' => 'Ольга', 'phone' => '+7 (912) 345-67-89', 'from_platform_id' => $platform->id],
        ]))->assertRedirect(route('order.index'));

        $client = Client::where('name', 'Ольга')->sole();

        expect($client->phone)->toBe('+79123456789')
            ->and(Order::sole()->client_id)->toBe($client->id);
    });

    test('оплачено больше суммы — ошибка', function () {
        $this->post(route('order.store'), ($this->orderData)(['amount_total' => '1 000', 'amount_payed' => '2 000']))
            ->assertSessionHasErrors('amount_payed');

        expect(Order::count())->toBe(0);
    });

    test('нецелая, нечисловая и отрицательная сумма — ошибка', function (string $amount) {
        $this->post(route('order.store'), ($this->orderData)(['amount_total' => $amount, 'amount_payed' => '0']))
            ->assertSessionHasErrors('amount_total');

        expect(Order::count())->toBe(0);
    })->with(['abc', '19.99', '-5']);

    test('товары только из списка', function () {
        $this->post(route('order.store'), ($this->orderData)(['product_ids' => [999_999]]))
            ->assertSessionHasErrors('product_ids');
    });
});

describe('редактирование и удаление', function () {
    test('форма показывает суммы в рублях', function () {
        $order = Order::factory()->for($this->client)->create(['amount_total' => 1_234_500, 'amount_payed' => 0]);

        $this->get(route('order.edit', $order))
            ->assertOk()
            ->assertSee('value="12345"', false);
    });

    test('обновляет поля и товары', function () {
        $order = Order::factory()->for($this->client)->create(['amount_total' => 100_000, 'amount_payed' => 0]);
        $other = Product::factory()->create();

        $this->patch(route('order.update', $order), [
            'status' => Order::STATUS_PROGRESS,
            'date_issue' => '2026-12-01T10:30',
            'description' => 'Новое описание',
            'amount_total' => '250 000',
            'amount_payed' => '250 000',
            'product_ids' => [$other->id],
        ])->assertRedirect(route('order.edit', $order));

        $order->refresh()->load('products');

        expect($order->status)->toBe(Order::STATUS_PROGRESS)
            ->and($order->amount_total)->toBe(25_000_000)
            ->and($order->amount_payed)->toBe(25_000_000)
            ->and($order->description)->toBe('Новое описание')
            ->and($order->products->pluck('id')->all())->toBe([$other->id]);
    });

    test('удаление мягкое, заказ можно восстановить', function () {
        $order = Order::factory()->for($this->client)->create();

        $this->delete(route('order.delete', $order))->assertSessionHas(Alert::TYPE_SUCCESS);
        $this->assertSoftDeleted($order);

        $this->post(route('order.restore', $order->id))->assertSessionHas(Alert::TYPE_SUCCESS);
        $this->assertNotSoftDeleted($order);
    });
});

describe('быстрая смена статуса', function () {
    test('через AJAX возвращает JSON', function () {
        $order = Order::factory()->for($this->client)->create(['status' => Order::STATUS_ACCEPT]);

        $this->patchJson(route('order.updateStatus', $order), ['status' => Order::STATUS_COMPLETE])
            ->assertOk()
            ->assertExactJson([
                'status' => Order::STATUS_COMPLETE,
                'label' => 'Выдан',
                'classes' => 'text-green-700 bg-green-100',
                'message' => 'Заказ №' . $order->id . ': статус «Выдан»',
            ]);

        expect($order->refresh()->status)->toBe(Order::STATUS_COMPLETE);
    });

    test('без JS — редирект назад', function () {
        $order = Order::factory()->for($this->client)->create(['status' => Order::STATUS_ACCEPT]);

        $this->from(route('order.index'))
            ->patch(route('order.updateStatus', $order), ['status' => Order::STATUS_PROGRESS])
            ->assertRedirect(route('order.index'))
            ->assertSessionHas(Alert::TYPE_SUCCESS);

        expect($order->refresh()->status)->toBe(Order::STATUS_PROGRESS);
    });

    test('неизвестный статус — 422, статус не меняется', function () {
        $order = Order::factory()->for($this->client)->create(['status' => Order::STATUS_ACCEPT]);

        $this->patchJson(route('order.updateStatus', $order), ['status' => 'HACK'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('status');

        expect($order->refresh()->status)->toBe(Order::STATUS_ACCEPT);
    });

    test('удалённый заказ — 404', function () {
        $order = Order::factory()->for($this->client)->create();
        $order->delete();

        $this->patchJson(route('order.updateStatus', $order->id), ['status' => Order::STATUS_COMPLETE])
            ->assertNotFound();
    });
});
