<?php

use App\Http\Controllers\ClientController;
use App\Http\Controllers\OrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect(route('moonshine.index'));
});

Route::middleware('auth:moonshine')->group(function () {
    Route::post('/clients/check', [ClientController::class, 'checkClient'])->name('client.checkClient');

    Route::post('/orders/store', [OrderController::class, 'store'])->name('order.store');
    Route::patch('/orders/{order}/update', [OrderController::class, 'update'])->name('order.update');
});
