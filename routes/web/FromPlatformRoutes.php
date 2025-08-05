<?php

use App\Http\Controllers\FromPlatformController;
use Illuminate\Support\Facades\Route;

Route::middleware('superAdmin')->group(function () {
    Route::post('/from-platforms', [FromPlatformController::class, 'store'])->name('fromPlatform.store');
    Route::get('/from-platforms/{fromPlatform}', [FromPlatformController::class, 'edit'])->name('fromPlatform.edit');
    Route::patch('/from-platforms/{fromPlatform}', [FromPlatformController::class, 'update'])->name('fromPlatform.update');
    Route::delete('/from-platforms/{fromPlatform}', [FromPlatformController::class, 'delete'])->name('fromPlatform.delete');
    Route::post('/from-platforms/{fromPlatform}', [FromPlatformController::class, 'restore'])->name('fromPlatform.restore')
        ->withTrashed();
});
