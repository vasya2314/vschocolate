<?php

use Illuminate\Support\Facades\Schedule;

//Artisan::command('inspire', function () {
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote');

Schedule::command('app:tg-orders-notification')->dailyAt('8:00')->withoutOverlapping(); // Уведомление о заказах
