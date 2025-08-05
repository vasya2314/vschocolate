<?php

namespace App\Http\Controllers;

use App\Actions\Product\DeleteProductAction;
use App\Actions\Product\RestoreProductAction;
use App\Actions\Product\StoreProductAction;
use App\Actions\Product\UpdateProductAction;
use App\Http\Requests\Product\StoreRequest;
use App\Http\Requests\Product\UpdateRequest;
use App\Models\Product;
use App\View\Components\Alert;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Foundation\Application;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function store(StoreRequest $request, StoreProductAction $storeProductAction): RedirectResponse
    {
        try {
            $storeProductAction->handle($request->input('name'));

            return redirect()->route('settings.index')
                ->with(Alert::TYPE_SUCCESS, 'Добавлен новый товар');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->onlyInput('name');
        }
    }

    public function edit(Product $product): View|Application|Factory
    {
        return view('pages.products.edit', compact('product'));
    }

    public function update(UpdateRequest $request, Product $product, UpdateProductAction $updateProductAction): RedirectResponse
    {
        try {
            $updateProductAction->handle(
                $request->validated(),
                $product
            );

            return redirect()->route('settings.index')
                ->with(Alert::TYPE_SUCCESS, 'Товар обновлен');
        } catch (\Exception $e) {
            return back()->with(Alert::TYPE_ERROR, $e->getMessage())
                ->onlyInput('name');
        }
    }

    public function delete(Product $product, DeleteProductAction $deleteProductAction): RedirectResponse
    {
        $deleteProductAction->handle($product);

        return redirect()->route('settings.index')
            ->with(Alert::TYPE_SUCCESS, 'Товар удален');
    }

    public function restore(Product $product, RestoreProductAction $restoreProductAction): RedirectResponse
    {
        $restoreProductAction->handle($product);

        return redirect()->route('settings.index')
            ->with(Alert::TYPE_SUCCESS, 'Товар восстановлен');
    }

}
