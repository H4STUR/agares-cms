<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'maintenance' => \App\Http\Middleware\CheckMaintenanceMode::class,
            'setting'     => \App\Http\Middleware\EnsureSetting::class,
            'api.key'     => \App\Http\Middleware\ApiKeyAuth::class,
        ]);

        // Payment gateway webhooks must not be CSRF-verified
        $middleware->validateCsrfTokens(except: [
            'shop/payment/webhook/*',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
