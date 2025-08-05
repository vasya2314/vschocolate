<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('superAdmin')->group(function () {
    Route::post('/products', [ProductController::class, 'store'])->name('product.store');
    Route::get('/products/{product}', [ProductController::class, 'edit'])->name('product.edit');
    Route::patch('/products/{product}', [ProductController::class, 'update'])->name('product.update');
    Route::delete('/products/{product}', [ProductController::class, 'delete'])->name('product.delete');
    Route::post('/products/{product}', [ProductController::class, 'restore'])->name('product.restore')
        ->withTrashed();
});
