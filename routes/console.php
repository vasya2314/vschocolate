<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('app:tg-orders-notification')->dailyAt('8:00')->withoutOverlapping(); // Заказы к выдаче с кнопками
Schedule::command('app:tg-daily-summary')->dailyAt('20:00')->withoutOverlapping(); // Итог дня
Schedule::command('app:tg-weekly-report')->sundays()->at('20:30')->withoutOverlapping(); // Отчёт за неделю
Schedule::command('app:tg-unpaid-reminder')->days([1, 4])->at('11:00')->withoutOverlapping(); // Должники: пн и чт
