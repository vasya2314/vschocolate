<?php

use App\Actions\TgNotification\GetTgChatIdAction;
use App\Actions\TgNotification\GetTgStatusAction;
use App\Actions\TgNotification\GetTgTokenAction;
use App\Constants\TgStatus;
use App\Models\FromPlatform;
use App\Models\Product;
use App\View\Components\Alert;

// Раздел «Настройки» доступен только супер-администратору (см. AuthTest).

beforeEach(function () {
    $this->actingAsSuperAdmin();
});

test('страница настроек показывает товары и площадки', function () {
    Product::factory()->create(['name' => 'Трюфели']);
    FromPlatform::factory()->create(['name' => 'VK']);

    $this->get(route('settings.index'))->assertOk()->assertSee('Трюфели')->assertSee('VK');
});

test('сохраняет настройки Telegram', function () {
    $this->post(route('settings.saveTgSettings'), [
        'chat_id' => '-100500',
        'token' => 'TEST-TOKEN',
        'tg_status' => TgStatus::ENABLE,
    ])->assertRedirect(route('settings.index'))->assertSessionHas(Alert::TYPE_SUCCESS);

    expect(app(GetTgChatIdAction::class)->handle())->toBe('-100500')
        ->and(app(GetTgTokenAction::class)->handle())->toBe('TEST-TOKEN')
        ->and(app(GetTgStatusAction::class)->handle())->toBe(TgStatus::ENABLE);
});

test('неизвестный статус Telegram отклоняется', function () {
    $this->post(route('settings.saveTgSettings'), ['tg_status' => 'MAYBE'])
        ->assertSessionHasErrors('tg_status');
});

describe('товары', function () {
    test('создание, изменение, удаление и восстановление', function () {
        $this->post(route('product.store'), ['name' => 'Трюфели'])
            ->assertRedirect(route('settings.index'))
            ->assertSessionHas(Alert::TYPE_SUCCESS);
        $product = Product::where('name', 'Трюфели')->sole();

        $this->patch(route('product.update', $product), ['name' => 'Трюфели ассорти'])
            ->assertRedirect(route('settings.index'));
        expect($product->refresh()->name)->toBe('Трюфели ассорти');

        $this->delete(route('product.delete', $product));
        $this->assertSoftDeleted($product);

        $this->post(route('product.restore', $product->id));
        $this->assertNotSoftDeleted($product);
    });

    test('название уникально', function () {
        Product::factory()->create(['name' => 'Трюфели']);

        $this->post(route('product.store'), ['name' => 'Трюфели'])->assertSessionHasErrors('name');
    });
});

describe('площадки', function () {
    test('создание, изменение, удаление и восстановление', function () {
        $this->post(route('fromPlatform.store'), ['name' => 'VK'])->assertSessionHas(Alert::TYPE_SUCCESS);
        $platform = FromPlatform::where('name', 'VK')->sole();

        $this->patch(route('fromPlatform.update', $platform), ['name' => 'ВКонтакте']);
        expect($platform->refresh()->name)->toBe('ВКонтакте');

        $this->delete(route('fromPlatform.delete', $platform));
        $this->assertSoftDeleted($platform);

        $this->post(route('fromPlatform.restore', $platform->id));
        $this->assertNotSoftDeleted($platform);
    });

    test('название уникально', function () {
        FromPlatform::factory()->create(['name' => 'VK']);

        $this->post(route('fromPlatform.store'), ['name' => 'VK'])->assertSessionHasErrors('name');
    });
});
