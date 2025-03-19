<?php

namespace App\Services;

use App\DTO\Client\UpdateClientDTO;
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


    public function updateClient(Client $client, UpdateClientDTO $updateClientDTO): Client
    {
        $data = [];

        if ($updateClientDTO->getName() !== null)
        {
            $data['name'] = $updateClientDTO->getName();
        }

        if ($updateClientDTO->getPhone() !== null)
        {
            $data['phone'] = $updateClientDTO->getPhone();
        }

        if ($updateClientDTO->getFrom() !== null)
        {
            $data['from'] = $updateClientDTO->getFrom();
        }

        if ($updateClientDTO->getComment() !== null)
        {
            $data['comment'] = $updateClientDTO->getComment();
        }

        if (!empty($data)) $client->update($data);

        return $client;
    }

}
