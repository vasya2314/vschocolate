<?php

use App\Exceptions\BusinessException;
use App\Http\Middleware\SuperAdminMiddleware;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'superAdmin' => SuperAdminMiddleware::class,
        ]);

        // Telegram не передаёт CSRF-токен; запрос проверяется секретом в контроллере
        $middleware->validateCsrfTokens(except: ['tg/webhook']);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->report(function (BusinessException $e) {
            // ...
        })->stop();
    })->create();
