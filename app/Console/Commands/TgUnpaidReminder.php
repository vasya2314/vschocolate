<?php

namespace App\Console\Commands;

use App\Actions\TgNotification\PrepareUnpaidReminderAction;
use App\Services\TgBot;
use Illuminate\Console\Command;

class TgUnpaidReminder extends Command
{
    protected $signature = 'app:tg-unpaid-reminder';

    protected $description = 'TG: напоминание о выданных, но не оплаченных заказах';

    public function handle(TgBot $tgBot, PrepareUnpaidReminderAction $prepareUnpaidReminderAction): void
    {
        if (!$tgBot->isEnabled()) return;

        $message = $prepareUnpaidReminderAction->handle();

        if ($message !== null) $tgBot->notify($message);
    }
}
