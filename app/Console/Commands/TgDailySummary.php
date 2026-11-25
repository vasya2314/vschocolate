<?php

namespace App\Console\Commands;

use App\Actions\TgNotification\PrepareDailySummaryAction;
use App\Services\TgBot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TgDailySummary extends Command
{
    protected $signature = 'app:tg-daily-summary {--date= : Дата в формате Y-m-d (по умолчанию сегодня)}';

    protected $description = 'TG: вечерний итог дня';

    public function handle(TgBot $tgBot, PrepareDailySummaryAction $prepareDailySummaryAction): void
    {
        if (!$tgBot->isEnabled()) return;

        $date = $this->option('date') ? Carbon::parse($this->option('date')) : null;

        $tgBot->notify($prepareDailySummaryAction->handle($date));
    }
}
