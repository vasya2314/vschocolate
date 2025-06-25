<?php

use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

//Artisan::command('inspire', function () {
//    /** @var ClosureCommand $this */
//    $this->comment(Inspiring::quote());
//})->purpose('Display an inspiring quote');

Schedule::command('app:tg-orders-notification')->dailyAt('7:00')->withoutOverlapping(); // Уведомление о заказах
