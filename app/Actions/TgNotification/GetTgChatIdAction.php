<?php

namespace App\Actions\TgNotification;

use Illuminate\Support\Facades\Cache;

class GetTgChatIdAction
{
    private string $key = 'tg_chat_id';

    public function handle(): mixed
    {
        return Cache::driver('database')->get($this->key);
    }
}
