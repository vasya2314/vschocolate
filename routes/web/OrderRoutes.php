<?php

use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/orders', [OrderController::class, 'index'])->name('order.index');
    Route::get('/orders/create', [OrderController::class, 'create'])->name('order.create');
    Route::post('/orders/store', [OrderController::class, 'store'])->name('order.store');
    Route::get('/orders/{order}', [OrderController::class, 'edit'])->name('order.edit');
    Route::patch('/orders/{order}', [OrderController::class, 'update'])->name('order.update');
    Route::delete('/orders/{order}', [OrderController::class, 'delete'])->name('order.delete');
    Route::post('/orders/{order}', [OrderController::class, 'restore'])->name('order.restore')
        ->withTrashed();
});
