<?php

use App\Http\Controllers\TgWebhookController;
use Illuminate\Support\Facades\Route;

// Webhook Telegram-бота: защищён секретом в заголовке X-Telegram-Bot-Api-Secret-Token
Route::post('/tg/webhook', [TgWebhookController::class, 'handle'])
    ->middleware('throttle:60,1')
    ->name('tg.webhook');
