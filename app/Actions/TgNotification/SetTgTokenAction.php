<?php

namespace App\Actions\TgNotification;

use App\Models\Setting;

class SetTgTokenAction
{
    private string $key = 'tg_token';

    public function handle(mixed $value): bool
    {
        return Setting::setValue($this->key, $value, encrypted: true);
    }
}
