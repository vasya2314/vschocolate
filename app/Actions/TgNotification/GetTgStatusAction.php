<?php

namespace App\Actions\TgNotification;

use Illuminate\Support\Facades\Cache;

class GetTgStatusAction
{
    private string $key = 'tg_status';

    public function handle(): mixed
    {
        return Cache::driver('database')->get($this->key);
    }
}
