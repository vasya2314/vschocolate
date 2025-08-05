<?php

namespace App\Actions\Client;

use App\Models\Client;

class RestoreClientAction
{
    public function handle(Client $client): ?bool
    {
        return $client->restore();
    }
}
