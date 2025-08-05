<?php

namespace App\Actions\TgNotification;

use Illuminate\Support\Facades\Cache;

class SetTgStatusAction
{
    private string $key = 'tg_status';

    public function handle(mixed $value): bool
    {
        return Cache::driver('database')->put($this->key, $value);
    }
}
