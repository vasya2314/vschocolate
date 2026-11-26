<?php

namespace App\Console\Commands;

use App\Actions\TgNotification\GetTgChatIdAction;
use App\Actions\TgNotification\GetTgStatusAction;
use App\Actions\TgNotification\GetTgTokenAction;
use App\Actions\TgNotification\PrepareMessageAction;
use App\Actions\TgNotification\SendNotificationAction;
use App\Constants\TgStatus;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

class TgOrdersNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:tg-orders-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'TG orders notification';

    private LoggerInterface $logger;

    /**
     * Execute the console command.
     */
    public function handle(
        SendNotificationAction $sendNotificationAction,
        PrepareMessageAction $prepareMessageAction,

        GetTgChatIdAction $getTgChatIdAction,
        GetTgTokenAction $getTgTokenAction,
        GetTgStatusAction $getTgStatusAction,
    ): void
    {
        if($getTgStatusAction->handle() == TgStatus::DISABLE) exit();

        $this->logger = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/tg-notify.log'),
        ]);

        // 2 + 1 потому что последний день не включается
        $message = $prepareMessageAction->handle(3);

        try {
            $sendNotificationAction->handle($message, $getTgChatIdAction->handle(), $getTgTokenAction->handle());
        } catch (\Exception $e) {
            $this->logger->error($e);
        }
    }
}
