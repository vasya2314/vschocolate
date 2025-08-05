<?php

namespace App\Actions\FromPlatform;

use App\Exceptions\BusinessException;
use App\Models\FromPlatform;

class UpdateFromPlatformAction
{
    /**
     * @throws BusinessException
     */
    public function handle(array $fields, FromPlatform $fromPlatform): FromPlatform
    {
        $data = [];

        if(array_key_exists('name', $fields)) $data['name'] = $fields['name'];

        if (isset($data['name']) && FromPlatform::findByName($data['name'], true, $fromPlatform->id)->exists())
        {
            throw new BusinessException('Платформа с таким названием уже существует');
        }

        $fromPlatform->update($data);

        return $fromPlatform->refresh();
    }
}
