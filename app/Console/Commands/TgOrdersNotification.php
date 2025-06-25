<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
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
    protected $description = '';

    private LoggerInterface $logger;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->logger = Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/tg-notify.log'),
        ]);

        $message = "";

        $now = Carbon::now();
        $nowString = $now->toDateString();

        // 2 + 1 потому что последний день не включается
        $nowPlusString = $now->addDays(3)->toDateString();

        $data = Order::with(['client', 'contents'])
            ->whereBetween('date_issue', [$nowString, $nowPlusString])
            ->orderBy('date_issue', 'ASC')
            ->get()
            ->mapToGroups(function ($item) {
                $carbonDate = Carbon::parse($item->date_issue);
                $carbonDateString = $carbonDate->format('d.m.Y');

                return [$carbonDateString => $item];
            });

        if($data->isEmpty())
        {
            $message = "Заказов к выдаче не найдено :(";
        } else {
            foreach ($data as $date => $row)
            {
                $message .= "<b>" . $date . ":" . "</b>\n";

                if($row->isEmpty())
                {
                    $message .= "Заказов не найдено :( \n";
                } else {
                    foreach($row as $order)
                    {
                        $message .= "🛒 | Заказ №" . $order->id . " (" . $this->getContents($order->contents) . ") \n";
                        $message .= "Сумма: " . kopToRubView($order->amount_total) . ". (оплачено: " . kopToRubView($order->amount_payed) . ")" . "\n";
                        $message .= "Статус: " . $order->status_view . "\n";

                        if($row->last() !== $order) $message .= "\n";
                    }
                }

                if($data->last() !== $row)
                {
                    $message .= "—————————————\n";
                }
            }
        }

        $response = Http::asForm()->post('https://api.telegram.org/bot' . config('tg-notify.token') . '/sendMessage', [
            'chat_id' => config('tg-notify.chat_id'),
            'text' => $message,
            'parse_mode' => 'html',
        ]);

        if($response->status() !== 200) $this->logger->info($response->json()['description']);
    }

    private function getContents(Collection $contents): string
    {
        $result = [];

        if($contents->isNotEmpty())
        {
            foreach ($contents as $content)
            {
                $result[] = $content->content_view;
            }

            return implode(', ', $result);
        }

        return '';
    }
}
