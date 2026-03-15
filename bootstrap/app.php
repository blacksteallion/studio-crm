<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // <--- Ensures your new api.php file is loaded
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        
        // Registering the Location Middleware for all web routes
        $middleware->web(append: [
            \App\Http\Middleware\ActiveLocationMiddleware::class,
        ]);

        // Exclude Meta Webhooks from CSRF protection
        $middleware->validateCsrfTokens(except: [
            'api/whatsapp/webhook',
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();