<?php

use App\Rules\InWhitelist;

function inWhitelistFails(array $whitelist, mixed $value): bool
{
    $failed = false;
    (new InWhitelist($whitelist))->validate('field', $value, function () use (&$failed) {
        $failed = true;
    });

    return $failed;
}

test('принимает массив и строку через запятую из белого списка', function () {
    expect(inWhitelistFails(['ACCEPT', 'COMPLETE'], ['ACCEPT']))->toBeFalse()
        ->and(inWhitelistFails(['ACCEPT', 'COMPLETE'], 'ACCEPT,COMPLETE'))->toBeFalse();
});

test('отклоняет значение не из списка', function () {
    expect(inWhitelistFails(['ACCEPT', 'COMPLETE'], ['ACCEPT', 'HACK']))->toBeTrue()
        ->and(inWhitelistFails([1, 2], '3'))->toBeTrue();
});
