<?php

namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    /**
     * Защита от случайного запуска на рабочей базе: RefreshDatabase пересоздаёт схему.
     * Разрешена только база с именем *_testing (phpunit.xml) или SQLite в памяти.
     * setUpTraits() вызывается после создания приложения, но до запуска трейтов.
     */
    protected function setUpTraits()
    {
        $database = DB::connection()->getDatabaseName();

        if ($database !== ':memory:' && !str_ends_with($database, '_testing')) {
            throw new RuntimeException(
                "Тесты должны работать на SQLite в памяти или базе *_testing, а подключены к «{$database}». "
                . 'Проверьте phpunit.xml и сбросьте кэш конфига: php artisan config:clear'
            );
        }

        return parent::setUpTraits();
    }

    protected function actingAsUser(): User
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        return $user;
    }

    protected function actingAsSuperAdmin(): User
    {
        $user = User::factory()->superAdmin()->create();
        $this->actingAs($user);

        return $user;
    }
}
