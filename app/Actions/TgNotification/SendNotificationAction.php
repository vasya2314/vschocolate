<?php

namespace App\Actions\TgNotification;

use App\Models\Product;
use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SendNotificationAction
{
    /**
     * @throws ConnectionException
     * @throws \Exception
     */
    public function handle(string $message, string $chatId, string $token): PromiseInterface|Response
    {
        $response = Http::asForm()->post(config('tg.bot_api_url') . $token . '/sendMessage', [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'html',
        ]);

        if($response->status() !== 200) throw new \Exception($response->json()['description']);

        return $response;
    }
}
