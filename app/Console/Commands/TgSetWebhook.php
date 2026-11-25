<?php

namespace App\Console\Commands;

use App\Services\TgBot;
use Illuminate\Console\Command;

class TgSetWebhook extends Command
{
    protected $signature = 'app:tg-set-webhook {--url= : Публичный HTTPS-адрес webhook (по умолчанию route("tg.webhook") от APP_URL)}';

    protected $description = 'TG: подключить webhook для кнопок в сообщениях бота';

    public function handle(TgBot $tgBot): int
    {
        $url = $this->option('url') ?: route('tg.webhook');

        try {
            $tgBot->setWebhook($url);
        } catch (\Exception $e) {
            $this->error($e->getMessage());

            return self::FAILURE;
        }

        $this->info('Webhook подключён: ' . $url);

        return self::SUCCESS;
    }
}
