<?php

use App\Http\Controllers\SettingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('superAdmin')->group(function () {
    Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
    Route::post('/settings/setTgSettings', [SettingsController::class, 'saveTgSettings'])->name('settings.saveTgSettings');
});
