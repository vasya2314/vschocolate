<?php

namespace App\Actions\TgNotification;

use Illuminate\Support\Facades\Cache;

class GetTgTokenAction
{
    private string $key = 'tg_token';

    public function handle(): mixed
    {
        return Cache::driver('database')->get($this->key);
    }
}
