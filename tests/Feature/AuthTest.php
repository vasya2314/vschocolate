<?php

use App\Models\User;

test('гостя отправляет на вход с корня и закрытых страниц', function (string $url) {
    $this->get($url)->assertRedirect(route('login'));
})->with(['/', '/dashboard', '/orders', '/orders/calendar', '/clients']);

test('страница входа доступна гостю', function () {
    $this->get(route('login'))->assertOk();
});

test('вход с верными данными', function () {
    $user = User::factory()->create(['password' => 'secret-pass']);

    $this->post(route('auth.login'), ['email' => $user->email, 'password' => 'secret-pass'])
        ->assertRedirect(route('dashboard.index'));

    $this->assertAuthenticatedAs($user);
});

test('вход с неверным паролем не проходит', function () {
    $user = User::factory()->create(['password' => 'secret-pass']);

    $this->from(route('login'))
        ->post(route('auth.login'), ['email' => $user->email, 'password' => 'wrong'])
        ->assertRedirect(route('login'))
        ->assertSessionHasErrors('email');

    $this->assertGuest();
});

test('выход', function () {
    $this->actingAsUser();

    $this->get(route('logout'))->assertRedirect(route('login'));

    $this->assertGuest();
});

test('команда создания супер-администратора', function () {
    $this->artisan('app:create-super-admin-user', ['email' => 'admin@example.test', 'password' => 'secret'])
        ->assertSuccessful();

    $user = User::findByEmail('admin@example.test')->first();

    expect($user)->not->toBeNull()
        ->and($user->isSuperAdmin())->toBeTrue()
        ->and($user->email_verified_at)->not->toBeNull();
});

test('админские разделы закрыты для обычного пользователя', function () {
    $this->actingAsUser();

    $this->get(route('settings.index'))->assertForbidden();
    $this->post(route('product.store'), ['name' => 'Трюфели'])->assertForbidden();
});

test('админские разделы доступны супер-администратору', function () {
    $this->actingAsSuperAdmin();

    $this->get(route('settings.index'))->assertOk();
});
