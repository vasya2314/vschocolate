<?php

use App\Models\User;
use App\View\Components\Alert;
use Illuminate\Support\Facades\Hash;

test('гость не видит страницу профиля', function () {
    $this->get(route('profile.edit'))->assertRedirect(route('login'));
});

test('страница профиля показывает данные пользователя', function () {
    $user = $this->actingAsUser();

    $this->get(route('profile.edit'))
        ->assertOk()
        ->assertSee($user->email)
        ->assertSee($user->firstname);
});

test('в шапке есть ссылка на профиль', function () {
    $this->actingAsUser();

    $this->get(route('profile.edit'))->assertSee(route('profile.edit'), false);
});

describe('личные данные', function () {
    test('обновляет имя, фамилию и email', function () {
        $user = $this->actingAsUser();

        $this->patch(route('profile.update'), [
            'firstname' => 'Анна',
            'lastname' => 'Иванова',
            'email' => 'anna@example.com',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas(Alert::TYPE_SUCCESS);

        $user->refresh();
        expect($user->firstname)->toBe('Анна')
            ->and($user->lastname)->toBe('Иванова')
            ->and($user->email)->toBe('anna@example.com');
    });

    test('можно оставить свой email', function () {
        $user = $this->actingAsUser();

        $this->patch(route('profile.update'), ['email' => $user->email])
            ->assertSessionHasNoErrors();
    });

    test('email другого пользователя занять нельзя', function () {
        User::factory()->create(['email' => 'busy@example.com']);
        $this->actingAsUser();

        $this->patch(route('profile.update'), ['email' => 'busy@example.com'])
            ->assertSessionHasErrors('email');
    });

    test('email обязателен и должен быть корректным', function () {
        $this->actingAsUser();

        $this->patch(route('profile.update'), ['email' => ''])->assertSessionHasErrors('email');
        $this->patch(route('profile.update'), ['email' => 'not-an-email'])->assertSessionHasErrors('email');
    });

    test('роль через профиль не меняется', function () {
        $user = $this->actingAsUser();

        $this->patch(route('profile.update'), ['email' => $user->email, 'role' => User::ROLE_SUPER_ADMIN]);

        expect($user->refresh()->isSuperAdmin())->toBeFalse();
    });
});

describe('смена пароля', function () {
    test('меняет пароль при верном текущем', function () {
        $user = $this->actingAsUser();

        $this->put(route('profile.updatePassword'), [
            'current_password' => 'password',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ])->assertRedirect(route('profile.edit'))->assertSessionHas(Alert::TYPE_SUCCESS);

        expect(Hash::check('new-secret-123', $user->refresh()->password))->toBeTrue();
    });

    test('неверный текущий пароль отклоняется', function () {
        $user = $this->actingAsUser();

        $this->put(route('profile.updatePassword'), [
            'current_password' => 'wrong',
            'password' => 'new-secret-123',
            'password_confirmation' => 'new-secret-123',
        ])->assertSessionHasErrors('current_password');

        expect(Hash::check('password', $user->refresh()->password))->toBeTrue();
    });

    test('подтверждение должно совпадать', function () {
        $this->actingAsUser();

        $this->put(route('profile.updatePassword'), [
            'current_password' => 'password',
            'password' => 'new-secret-123',
            'password_confirmation' => 'other-secret-123',
        ])->assertSessionHasErrors('password');
    });

    test('слишком короткий пароль отклоняется', function () {
        $this->actingAsUser();

        $this->put(route('profile.updatePassword'), [
            'current_password' => 'password',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])->assertSessionHasErrors('password');
    });
});
