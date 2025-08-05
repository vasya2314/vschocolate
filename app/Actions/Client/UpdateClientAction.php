<?php

namespace App\Actions\Client;

use App\Exceptions\BusinessException;
use App\Models\Client;
use Propaganistas\LaravelPhone\PhoneNumber;

class UpdateClientAction
{
    /**
     * @throws BusinessException
     */
    public function handle(array $fields, Client $client): Client
    {
        $data = [];

        if(array_key_exists('name', $fields)) $data['name'] = $fields['name'];
        if(array_key_exists('phone', $fields)) $data['phone'] = (string) new PhoneNumber($fields['phone'], 'RU');
        if(array_key_exists('from_platform_id', $fields)) $data['from_platform_id'] = $fields['from_platform_id'];
        if(array_key_exists('comment', $fields)) $data['comment'] = $fields['comment'];

        if(isset($data['phone']) && Client::findByPhone($data['phone'], true, $client->id)->exists())
        {
            throw new BusinessException('Пользователь с телефоном уже существует');
        }

        $client->update($data);

        return $client->refresh();
    }
}
