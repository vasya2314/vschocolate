<?php

namespace App\Actions\TgNotification;

use App\Models\Setting;

class GetTgStatusAction
{
    private string $key = 'tg_status';

    public function handle(): mixed
    {
        return Setting::getValue($this->key);
    }
}
