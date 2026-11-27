<?php

namespace App\Actions\TgNotification;

use App\Models\Setting;

class GetTgTokenAction
{
    private string $key = 'tg_token';

    public function handle(): mixed
    {
        return Setting::getValue($this->key, encrypted: true);
    }
}
