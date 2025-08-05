<?php

use App\Http\Controllers\ClientController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::post('/clients/findByPhone', [ClientController::class, 'getClientByPhone'])->name('client.getClientByPhone');

    Route::get('/clients', [ClientController::class, 'index'])->name('client.index');
    Route::get('/clients/create', [ClientController::class, 'create'])->name('client.create');
    Route::post('/clients/store', [ClientController::class, 'store'])->name('client.store');
    Route::get('/clients/{client}', [ClientController::class, 'edit'])->name('client.edit');
    Route::patch('/clients/{client}', [ClientController::class, 'update'])->name('client.update');
    Route::delete('/clients/{client}', [ClientController::class, 'delete'])->name('client.delete');
    Route::post('/clients/{client}', [ClientController::class, 'restore'])->name('client.restore')
        ->withTrashed();
});
