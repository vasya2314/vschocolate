<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
| Feature-тесты работают на SQLite в памяти (см. phpunit.xml): RefreshDatabase прогоняет миграции,
| каждый тест идёт в транзакции. Tests\TestCase не даёт запуститься на рабочей базе.
*/
pest()->extend(TestCase::class)->use(RefreshDatabase::class)->in('Feature');

pest()->extend(TestCase::class)->in('Unit');

/**
 * Отрисовать блок resources/views/components/blocks/{name} и вернуть данные,
 * которые подготовил его view composer.
 */
function composeBlock(string $name, array $data = []): array
{
    $view = view("components.blocks.{$name}", $data);
    $view->render();

    return $view->getData();
}
