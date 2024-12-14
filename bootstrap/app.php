<?php

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ChefMiddleware;
use App\Http\Middleware\SetDBConnection;
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
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
            \Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets::class,
        ]);

        // Alias middleware for role-based access control
        $middleware->alias([
            'setDB' => SetDBConnection::class,
            'admin' => AdminMiddleware::class,
            'chef' => ChefMiddleware::class,
            
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Handle exceptions here if necessary
    })->create();
