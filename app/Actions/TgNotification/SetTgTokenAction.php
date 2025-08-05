<?php

namespace App\Actions\TgNotification;

use Illuminate\Support\Facades\Cache;

class SetTgTokenAction
{
    private string $key = 'tg_token';

    public function handle(mixed $value): bool
    {
        return Cache::driver('database')->put($this->key, $value);
    }
}
