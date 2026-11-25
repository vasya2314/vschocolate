<?php

namespace App\Http\Controllers;

use App\Actions\Order\UpdateOrderAction;
use App\Actions\TgNotification\PrepareMessageAction;
use App\Console\Commands\TgOrdersNotification;
use App\Models\Order;
use App\Services\TgBot;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Обработка нажатий на inline-кнопки бота (смена статуса заказа из утреннего сообщения).
 */
class TgWebhookController extends Controller
{
    /** Статусы, которые можно поставить кнопкой */
    private const ALLOWED_STATUSES = [Order::STATUS_PROGRESS, Order::STATUS_COMPLETE];

    public function handle(
        Request $request,
        TgBot $tgBot,
        UpdateOrderAction $updateOrderAction,
        PrepareMessageAction $prepareMessageAction,
    ): JsonResponse
    {
        if (!$tgBot->isValidWebhookSecret($request->header('X-Telegram-Bot-Api-Secret-Token'))) {
            abort(403);
        }

        $callback = $request->input('callback_query');
        if (!is_array($callback) || empty($callback['id'])) {
            return response()->json(['ok' => true]);
        }

        $callbackId = (string) $callback['id'];
        $chatId = $callback['message']['chat']['id'] ?? null;
        $messageId = $callback['message']['message_id'] ?? null;

        // Кнопки работают только в чате, указанном в настройках
        if ($chatId === null || (string) $chatId !== $tgBot->chatId()) {
            $tgBot->answerCallback($callbackId, 'Нет доступа');

            return response()->json(['ok' => true]);
        }

        $parts = explode('|', (string) ($callback['data'] ?? ''));
        if (count($parts) !== 4 || $parts[0] !== PrepareMessageAction::CALLBACK_PREFIX) {
            $tgBot->answerCallback($callbackId, 'Неизвестная команда');

            return response()->json(['ok' => true]);
        }

        [, $orderId, $status, $fromString] = $parts;
        $order = ctype_digit($orderId) ? Order::find((int) $orderId) : null;

        if (!$order || !in_array($status, self::ALLOWED_STATUSES, true)) {
            $tgBot->answerCallback($callbackId, 'Заказ не найден');

            return response()->json(['ok' => true]);
        }

        // Не откатываем статус назад: выданный не станет «выполненным», отменённый не меняем
        $isBackward = $order->status === Order::STATUS_CANCELED
            || ($order->status === Order::STATUS_COMPLETE && $status === Order::STATUS_PROGRESS);

        if ($isBackward || $order->status === $status) {
            $tgBot->answerCallback($callbackId, 'Заказ №' . $order->id . ' уже «' . $order->status_view . '»');
        } else {
            $updateOrderAction->handle(['status' => $status], $order);
            $tgBot->answerCallback($callbackId, 'Заказ №' . $order->id . ' → «' . $order->status_view . '»');
        }

        // Перерисовываем исходное сообщение, чтобы статусы и кнопки были актуальны
        if (preg_match('/^\d{8}$/', $fromString) && $messageId) {
            $from = Carbon::createFromFormat('!Ymd', $fromString);
            $message = $prepareMessageAction->handle(TgOrdersNotification::DAYS, $from);
            $tgBot->editMessage($chatId, (int) $messageId, $message['text'], $message['keyboard']);
        }

        return response()->json(['ok' => true]);
    }
}
