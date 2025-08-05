<?php

namespace App\Actions\Product;

use App\Exceptions\BusinessException;
use App\Models\Product;

class StoreProductAction
{
    /**
     * @throws BusinessException
     */
    public function handle(string $name): Product
    {
        if (Product::findByName($name, true)->exists())
        {
            throw new BusinessException('Товар с таким названием уже существует');
        }

        return Product::create([
            'name' => $name,
        ]);
    }
}
