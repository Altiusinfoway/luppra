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
        $middleware->web(append: [
            \App\Http\Middleware\InitializeTenancy::class,
            \App\Http\Middleware\EnsureTenantSessionIsValid::class,
            \App\Http\Middleware\EnsureCompanySettingsCompleted::class,
            \App\Http\Middleware\EnsureTenantDefaultsInitialized::class,
            \App\Http\Middleware\EnsureSuperAdminLandlordAccess::class,
            \App\Http\Middleware\EnsureTenantSubscriptionIsValid::class,
            \App\Http\Middleware\EnsureTenantModuleAccess::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\InitializeTenancy::class,
            \App\Http\Middleware\EnsureTenantSessionIsValid::class,
            \App\Http\Middleware\EnsureSuperAdminLandlordAccess::class,
            \App\Http\Middleware\EnsureTenantSubscriptionIsValid::class,
            \App\Http\Middleware\EnsureTenantModuleAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
