<?php

namespace App\Actions\TgNotification;

use Illuminate\Support\Facades\Cache;

class SetTgChatIdAction
{
    private string $key = 'tg_chat_id';

    public function handle(mixed $value): bool
    {
        return Cache::driver('database')->put($this->key, $value);
    }
}
