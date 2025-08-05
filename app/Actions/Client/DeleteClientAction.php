<?php

namespace App\Actions\Client;

use App\Models\Client;

class DeleteClientAction
{
    public function handle(Client $client): ?bool
    {
        return $client->delete();
    }
}
