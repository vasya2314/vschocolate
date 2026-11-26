<?php

use App\Actions\TgNotification\SetTgChatIdAction;
use App\Actions\TgNotification\SetTgStatusAction;
use App\Actions\TgNotification\SetTgTokenAction;
use App\Constants\TgStatus;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Psr\Log\LoggerInterface;

// Утреннее уведомление app:tg-orders-notification: заказы к выдаче на сегодня и два следующих дня.

beforeEach(function () {
    $this->travelTo('2026-11-18 08:00:00');
    // Ответ Telegram можно переопределить в тесте: $this->tgResponse = Http::response(...)
    $this->tgResponse = Http::response(['ok' => true, 'result' => []]);
    Http::fake(fn () => $this->tgResponse);

    app(SetTgTokenAction::class)->handle('TEST-TOKEN');
    app(SetTgChatIdAction::class)->handle('-100500');
    app(SetTgStatusAction::class)->handle(TgStatus::ENABLE);
});

test('отправляет заказы на сегодня и два следующих дня', function () {
    $today = Order::factory()->create(['date_issue' => '2026-11-18 15:00:00', 'amount_total' => 730_000, 'amount_payed' => 300_000, 'status' => Order::STATUS_ACCEPT]);
    $today->products()->attach(Product::factory()->create(['name' => 'Трюфели']));
    $dayAfter = Order::factory()->create(['date_issue' => '2026-11-20 10:00:00']);
    $tooLate = Order::factory()->create(['date_issue' => '2026-11-21 10:00:00']);
    $yesterday = Order::factory()->create(['date_issue' => '2026-11-17 10:00:00']);

    $this->artisan('app:tg-orders-notification')->assertSuccessful();

    Http::assertSentCount(1);
    Http::assertSent(function (Request $request) use ($today, $dayAfter, $tooLate, $yesterday) {
        $text = $request['text'];

        return str_ends_with($request->url(), 'TEST-TOKEN/sendMessage')
            && $request['chat_id'] === '-100500'
            && $request['parse_mode'] === 'html'
            && !isset($request['reply_markup'])
            && str_contains($text, '<b>18.11.2026:</b>')
            && str_contains($text, "Заказ №{$today->id} (Трюфели)")
            && str_contains($text, 'Сумма: ' . rub('7 300 ₽') . '. (оплачено: ' . rub('3 000 ₽') . ')')
            && str_contains($text, 'Статус: Принят')
            && str_contains($text, "Заказ №{$dayAfter->id}")
            && !str_contains($text, "Заказ №{$tooLate->id}")
            && !str_contains($text, "Заказ №{$yesterday->id}");
    });
});

test('если заказов нет — сообщает об этом', function () {
    $this->artisan('app:tg-orders-notification')->assertSuccessful();

    Http::assertSent(fn (Request $request) => $request['text'] === 'Заказов к выдаче не найдено :(');
});

test('выключенные уведомления ничего не отправляют', function () {
    app(SetTgStatusAction::class)->handle(TgStatus::DISABLE);
    Order::factory()->create(['date_issue' => '2026-11-18 15:00:00']);

    $this->artisan('app:tg-orders-notification');

    Http::assertNothingSent();
});

test('ошибка Telegram не роняет команду и пишется в лог', function () {
    $this->tgResponse = Http::response(['ok' => false, 'description' => 'Bad Request: chat not found'], 400);

    // Команда пишет ошибки в отдельный лог через Log::build — подменяем, чтобы не трогать storage/logs
    $logger = Mockery::mock(LoggerInterface::class);
    $logger->shouldReceive('error')->once()->withArgs(fn ($error) => str_contains((string) $error, 'chat not found'));
    Log::shouldReceive('build')->once()->andReturn($logger);

    $this->artisan('app:tg-orders-notification')->assertSuccessful();

    Http::assertSentCount(1);
});

test('команда стоит в расписании на 8:00', function () {
    $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
        ->first(fn ($event) => str_contains($event->command, 'app:tg-orders-notification'));

    expect($event)->not->toBeNull()
        ->and($event->expression)->toBe('0 8 * * *');
});
