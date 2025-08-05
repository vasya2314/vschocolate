<?php

namespace App\Actions\Client;

use App\Models\Client;

class FindByPhoneClientAction
{
    public function handle(string $phone, bool $withTrashed = false): ?Client
    {
        return Client::findByPhone($phone, $withTrashed)
            ->first();
    }
}
