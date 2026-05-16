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
            'maintenance'    => \App\Http\Middleware\CheckMaintenanceMode::class,
            'setting'        => \App\Http\Middleware\EnsureSetting::class,
            'api.key'        => \App\Http\Middleware\ApiKeyAuth::class,
            'two-factor.setup' => \App\Http\Middleware\Force2faSetup::class,
        ]);

        // Enforce 2FA enrolment on every web request that has an authenticated user.
        // The middleware short-circuits for guests and for users who aren't forced to enrol.
        $middleware->web(append: [
            \App\Http\Middleware\Force2faSetup::class,
        ]);

        // Payment gateway webhooks must not be CSRF-verified
        $middleware->validateCsrfTokens(except: [
            'shop/payment/webhook/*',
            'newsletter/external/webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
