<?php

namespace App\Services;

use App\Actions\TgNotification\GetTgChatIdAction;
use App\Actions\TgNotification\GetTgStatusAction;
use App\Actions\TgNotification\GetTgTokenAction;
use App\Constants\TgStatus;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;

/**
 * Обёртка над Telegram Bot API: отправка уведомлений в настроенный чат и обработка кнопок.
 */
class TgBot
{
    private const WEBHOOK_SECRET_KEY = 'tg_webhook_secret';

    /** Лимит Telegram на длину сообщения */
    public const MAX_MESSAGE_LENGTH = 4096;

    public function __construct(
        private readonly GetTgTokenAction $getTgTokenAction,
        private readonly GetTgChatIdAction $getTgChatIdAction,
        private readonly GetTgStatusAction $getTgStatusAction,
    ) {}

    public function isEnabled(): bool
    {
        return $this->getTgStatusAction->handle() === TgStatus::ENABLE
            && $this->getTgTokenAction->handle()
            && $this->getTgChatIdAction->handle();
    }

    public function chatId(): ?string
    {
        $chatId = $this->getTgChatIdAction->handle();

        return $chatId !== null ? (string) $chatId : null;
    }

    /**
     * Отправить сообщение в настроенный чат, если уведомления включены.
     * Ошибки не пробрасываются, а пишутся в storage/logs/tg-notify.log.
     *
     * @param array|null $keyboard строки inline-кнопок: [[['text' => ..., 'callback_data' => ...]], ...]
     */
    public function notify(string $text, ?array $keyboard = null): bool
    {
        if (!$this->isEnabled()) return false;

        try {
            $this->call('sendMessage', array_filter([
                'chat_id' => $this->chatId(),
                'text' => $text,
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
                'reply_markup' => $keyboard ? json_encode(['inline_keyboard' => $keyboard]) : null,
            ]));

            return true;
        } catch (\Throwable $e) {
            $this->logger()->error($e);

            return false;
        }
    }

    public function editMessage(string|int $chatId, int $messageId, string $text, ?array $keyboard): void
    {
        try {
            $this->call('editMessageText', [
                'chat_id' => $chatId,
                'message_id' => $messageId,
                'text' => $text,
                'parse_mode' => 'html',
                'disable_web_page_preview' => true,
                'reply_markup' => json_encode(['inline_keyboard' => $keyboard ?? []]),
            ]);
        } catch (\Throwable $e) {
            // Текст и кнопки не изменились — это не ошибка
            if (!str_contains($e->getMessage(), 'message is not modified')) {
                $this->logger()->error($e);
            }
        }
    }

    public function answerCallback(string $callbackId, string $text): void
    {
        try {
            $this->call('answerCallbackQuery', [
                'callback_query_id' => $callbackId,
                'text' => $text,
            ]);
        } catch (\Throwable $e) {
            $this->logger()->error($e);
        }
    }

    /**
     * Зарегистрировать webhook для обработки нажатий на кнопки.
     * Секрет сохраняется и затем сверяется с заголовком X-Telegram-Bot-Api-Secret-Token.
     *
     * @throws \Exception
     */
    public function setWebhook(string $url): void
    {
        $secret = Str::random(64);

        $this->call('setWebhook', [
            'url' => $url,
            'secret_token' => $secret,
            'allowed_updates' => json_encode(['callback_query']),
            'drop_pending_updates' => true,
        ]);

        Cache::driver('database')->forever(self::WEBHOOK_SECRET_KEY, $secret);
    }

    public function isValidWebhookSecret(?string $secret): bool
    {
        $expected = Cache::driver('database')->get(self::WEBHOOK_SECRET_KEY);

        return is_string($expected) && is_string($secret) && hash_equals($expected, $secret);
    }

    /**
     * @throws \Exception
     */
    public function call(string $method, array $params): Response
    {
        $token = $this->getTgTokenAction->handle();
        if (!$token) throw new \Exception('Не задан токен Telegram-бота');

        $response = Http::asForm()
            ->timeout(15)
            ->post(config('tg.bot_api_url') . $token . '/' . $method, $params);

        if (!$response->successful() || !$response->json('ok')) {
            throw new \Exception('Telegram ' . $method . ': ' . ($response->json('description') ?? $response->status()));
        }

        return $response;
    }

    public function logger(): LoggerInterface
    {
        return Log::build([
            'driver' => 'single',
            'path' => storage_path('logs/tg-notify.log'),
        ]);
    }
}
