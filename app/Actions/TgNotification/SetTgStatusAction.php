<?php

namespace App\Actions\TgNotification;

use App\Models\Setting;

class SetTgStatusAction
{
    private string $key = 'tg_status';

    public function handle(mixed $value): bool
    {
        return Setting::setValue($this->key, $value);
    }
}
