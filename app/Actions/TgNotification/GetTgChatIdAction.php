<?php

namespace App\Actions\TgNotification;

use App\Models\Setting;

class GetTgChatIdAction
{
    private string $key = 'tg_chat_id';

    public function handle(): mixed
    {
        return Setting::getValue($this->key);
    }
}
