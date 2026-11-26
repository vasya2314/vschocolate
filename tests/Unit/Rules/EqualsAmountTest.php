<?php

use App\Rules\EqualsAmount;

// «Оплачено» не может быть больше суммы заказа (обе суммы в копейках).

function equalsAmountErrors(EqualsAmount $rule, mixed $value): array
{
    $errors = [];
    $rule->validate('amount_payed', $value, function (string $message) use (&$errors) {
        $errors[] = $message;
    });

    return $errors;
}

test('проходит, если оплачено не больше суммы', function () {
    expect(equalsAmountErrors(new EqualsAmount(100_000), 50_000))->toBe([])
        ->and(equalsAmountErrors(new EqualsAmount(100_000), 100_000))->toBe([]);
});

test('не проходит, если оплачено больше суммы', function () {
    expect(equalsAmountErrors(new EqualsAmount(100_000), 200_000))
        ->toBe(['Значение не может быть больше, чем 1000']);
});

test('пропускает нечисловые значения — их отклоняет правило integer', function () {
    expect(equalsAmountErrors(new EqualsAmount('abc'), 200_000))->toBe([])
        ->and(equalsAmountErrors(new EqualsAmount(100_000), 'xyz'))->toBe([]);
});
