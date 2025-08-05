<?php

namespace App\Actions\Product;

use App\Models\Product;

class RestoreProductAction
{
    public function handle(Product $product): ?bool
    {
        return $product->restore();
    }
}
