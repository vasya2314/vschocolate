<?php

namespace App\Actions\Product;

use App\Exceptions\BusinessException;
use App\Models\Product;

class UpdateProductAction
{
    /**
     * @throws BusinessException
     */
    public function handle(array $fields, Product $product): Product
    {
        $data = [];

        if(array_key_exists('name', $fields)) $data['name'] = $fields['name'];

        if (isset($data['name']) && Product::findByName($data['name'], true, $product->id)->exists())
        {
            throw new BusinessException('Товар с таким названием уже существует');
        }

        $product->update($data);

        return $product->refresh();
    }
}
