<?php

use App\Actions\TgNotification\GetTgChatIdAction;
use App\Actions\TgNotification\GetTgStatusAction;
use App\Actions\TgNotification\GetTgTokenAction;
use App\Actions\TgNotification\SetTgChatIdAction;
use App\Actions\TgNotification\SetTgStatusAction;
use App\Actions\TgNotification\SetTgTokenAction;
use App\Constants\TgStatus;
use App\Models\Setting;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

// Настройки Telegram хранятся в таблице settings, а не в кэше (раньше слетали после деплоя).

function saveTgSettings(): void
{
    app(SetTgTokenAction::class)->handle('123456:SECRET-TOKEN');
    app(SetTgChatIdAction::class)->handle('-100500');
    app(SetTgStatusAction::class)->handle(TgStatus::ENABLE);
}

test('настройки Telegram переживают очистку кэша при деплое', function () {
    config(['cache.default' => 'database']);
    saveTgSettings();

    Artisan::call('cache:clear');
    Cache::store('database')->flush();

    expect(app(GetTgTokenAction::class)->handle())->toBe('123456:SECRET-TOKEN')
        ->and(app(GetTgChatIdAction::class)->handle())->toBe('-100500')
        ->and(app(GetTgStatusAction::class)->handle())->toBe(TgStatus::ENABLE);
});

test('токен хранится в БД зашифрованным', function () {
    saveTgSettings();

    $raw = DB::table('settings')->where('key', 'tg_token')->value('value');

    expect($raw)->not->toContain('SECRET-TOKEN')
        ->and(Crypt::decryptString($raw))->toBe('123456:SECRET-TOKEN');
});

test('chat ID и статус хранятся как есть', function () {
    saveTgSettings();

    expect(DB::table('settings')->where('key', 'tg_chat_id')->value('value'))->toBe('-100500');
});

test('повторное сохранение перезаписывает значение', function () {
    saveTgSettings();
    app(SetTgTokenAction::class)->handle('NEW-TOKEN');

    expect(app(GetTgTokenAction::class)->handle())->toBe('NEW-TOKEN')
        ->and(Setting::count())->toBe(3);
});

test('нерасшифровываемый токен (сменился APP_KEY) не роняет приложение', function () {
    DB::table('settings')->insert(['key' => 'tg_token', 'value' => 'not-encrypted-garbage']);

    expect(app(GetTgTokenAction::class)->handle())->toBeNull();

    $this->actingAsSuperAdmin();
    $this->get(route('settings.index'))->assertOk();
});

test('без сохранённых настроек возвращается null', function () {
    expect(app(GetTgTokenAction::class)->handle())->toBeNull()
        ->and(app(GetTgChatIdAction::class)->handle())->toBeNull();
});
