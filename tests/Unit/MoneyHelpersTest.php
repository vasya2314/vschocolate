<?php

// Хелперы денег из app/_export.php. Суммы хранятся в целых копейках.

test('rubToKop переводит целые рубли в целые копейки', function () {
    expect(rubToKop(12_345))->toBe(1_234_500)
        ->and(rubToKop(0))->toBe(0);
});

test('kopToRub возвращает рубли с копейками', function () {
    expect(kopToRub(123_456))->toBe(1234.56)
        ->and(kopToRub(0))->toBe(0.0);
});

test('kopToRubView форматирует с разделителем тысяч и знаком рубля', function () {
    expect(kopToRubView(1_234_500))->toBe('12 345 ₽')
        ->and(kopToRubView(0))->toBe('0 ₽')
        ->and(kopToRubView('150000'))->toBe('1 500 ₽');
});

test('kopToRubShort даёт короткие подписи для осей', function (int $kop, string $expected) {
    expect(kopToRubShort($kop))->toBe($expected);
})->with([
    'рубли' => [90_000, '900 ₽'],
    'тысячи' => [35_000_000, '350 тыс ₽'],
    'миллион ровно' => [200_000_000, '2 млн ₽'],
    'миллион с дробью' => [150_000_000, '1,5 млн ₽'],
    'ноль' => [0, '0 ₽'],
]);
