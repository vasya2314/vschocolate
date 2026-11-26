<?php

use App\Models\Order;

// Вычисляемые поля заказа (без обращения к базе).

test('долг — неоплаченная часть в копейках, не меньше нуля', function () {
    expect((new Order(['amount_total' => 100_000, 'amount_payed' => 30_000]))->debt)->toBe(70_000)
        ->and((new Order(['amount_total' => 100_000, 'amount_payed' => 100_000]))->debt)->toBe(0)
        ->and((new Order(['amount_total' => 100_000, 'amount_payed' => 150_000]))->debt)->toBe(0);
});

test('суммы приводятся к целым числам', function () {
    $order = new Order(['amount_total' => '150000', 'amount_payed' => '0']);

    expect($order->amount_total)->toBe(150_000)
        ->and($order->amount_payed)->toBe(0);
});

test('название и цвет бейджа статуса', function () {
    $order = new Order(['status' => Order::STATUS_COMPLETE]);

    expect($order->status_view)->toBe('Выдан')
        ->and($order->status_classes)->toBe('text-green-700 bg-green-100');
});

test('новый заказ по умолчанию «Принят»', function () {
    expect((new Order())->status)->toBe(Order::STATUS_ACCEPT);
});

test('у каждого статуса есть название и цвет', function (string $status) {
    $order = new Order(['status' => $status]);

    expect($order->status_view)->not->toBeEmpty()
        ->and($order->status_classes)->not->toBeEmpty();
})->with(Order::getAllStatuses());
