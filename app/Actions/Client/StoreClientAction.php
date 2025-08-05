<?php

namespace App\Actions\Client;

use App\Exceptions\BusinessException;
use App\Models\Client;
use Propaganistas\LaravelPhone\PhoneNumber;

class StoreClientAction
{

    /**
     * @throws BusinessException
     */
    public function handle(array $fields): Client
    {
        $data = [
            'name' => $fields['name'],
            'phone' => (string) new PhoneNumber($fields['phone'], 'RU'),
            'from_platform_id' => $fields['from_platform_id'],
            'comment' => $fields['comment'] ?? null,
        ];

        if(Client::findByPhone($data['phone'], true)->exists())
        {
            throw new BusinessException('Пользователь с телефоном уже существует');
        }

        return Client::create($data);
    }
}
