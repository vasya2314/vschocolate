<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/*
| Feature-тесты работают с отдельной MySQL-базой lara_docker_testing (см. phpunit.xml);
| RefreshDatabase пересоздаёт её схему, а Tests\TestCase не даёт запуститься на другой базе.
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
