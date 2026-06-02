<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
//        $middleware->redirectGuestsTo('/backend/login');
        $middleware->encryptCookies(except: [
            'ckCsrfToken',
        ]);
        $middleware->validateCsrfTokens(
            except: [
                'ckfinder/*',
                'ttxt/webhook',
                'webhooks/ttxt',
            ]
        );
        $middleware->web(append: [
            \App\Http\Middleware\SecurityHeaders::class,
        ]);
        $middleware->alias([
            'active.user' => \App\Http\Middleware\EnsureActiveUser::class,
            'platform.owner' => \App\Http\Middleware\EnsurePlatformOwner::class,
            'tenant.organizer' => \App\Http\Middleware\EnsureTenantOrganizer::class,
            'tenant.db' => \App\Http\Middleware\UseTenantDatabase::class,
            'module.enabled' => \App\Http\Middleware\EnsureModuleEnabled::class,
            'permission' => \App\Http\Middleware\EnsurePermission::class,
        ]);
        $middleware->prependToPriorityList(
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\UseTenantDatabase::class
        );
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
