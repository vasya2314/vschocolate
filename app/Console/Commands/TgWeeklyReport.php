<?php

namespace App\Console\Commands;

use App\Actions\TgNotification\PrepareWeeklyReportAction;
use App\Services\TgBot;
use Carbon\Carbon;
use Illuminate\Console\Command;

class TgWeeklyReport extends Command
{
    protected $signature = 'app:tg-weekly-report {--date= : Любая дата недели в формате Y-m-d (по умолчанию текущая неделя)}';

    protected $description = 'TG: еженедельный отчёт с выручкой';

    public function handle(TgBot $tgBot, PrepareWeeklyReportAction $prepareWeeklyReportAction): void
    {
        if (!$tgBot->isEnabled()) return;

        $date = $this->option('date') ? Carbon::parse($this->option('date')) : null;

        $tgBot->notify($prepareWeeklyReportAction->handle($date));
    }
}
