<?php

namespace App\Console\Commands;

use App\Actions\TgNotification\PrepareMessageAction;
use App\Services\TgBot;
use Illuminate\Console\Command;

class TgOrdersNotification extends Command
{
    protected $signature = 'app:tg-orders-notification';

    protected $description = 'TG: заказы к выдаче на ближайшие дни с кнопками смены статуса';

    /** Сегодня + 2 дня (последний день не включается) */
    public const DAYS = 3;

    public function handle(TgBot $tgBot, PrepareMessageAction $prepareMessageAction): void
    {
        if (!$tgBot->isEnabled()) return;

        $message = $prepareMessageAction->handle(self::DAYS);

        $tgBot->notify($message['text'], $message['keyboard']);
    }
}
