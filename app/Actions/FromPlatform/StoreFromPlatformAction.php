<?php

namespace App\Actions\FromPlatform;

use App\Exceptions\BusinessException;
use App\Models\FromPlatform;

class StoreFromPlatformAction
{
    /**
     * @throws BusinessException
     */
    public function handle(string $name): FromPlatform
    {
        if (FromPlatform::findByName($name, true)->exists())
        {
            throw new BusinessException('Платформа с таким названием уже существует');
        }

        return FromPlatform::create([
            'name' => $name,
        ]);
    }
}
