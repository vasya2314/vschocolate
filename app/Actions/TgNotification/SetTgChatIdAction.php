<?php

namespace App\Actions\TgNotification;

use App\Models\Setting;

class SetTgChatIdAction
{
    private string $key = 'tg_chat_id';

    public function handle(mixed $value): bool
    {
        return Setting::setValue($this->key, $value);
    }
}
