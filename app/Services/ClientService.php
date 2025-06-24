<?php

namespace App\Services;

use App\DTO\ClientDTO;
use App\Models\Client;

class ClientService
{
    public function createClient(ClientDTO $createClientDTO): Client
    {
        $data = [
            'name' => $createClientDTO->getName(),
            'phone' => $createClientDTO->getPhone(),
            'from' => $createClientDTO->getFrom(),
            'comment' => $createClientDTO->getComment(),
        ];

        $client = new Client($data);
        $client->save();

        return $client;
    }
}
