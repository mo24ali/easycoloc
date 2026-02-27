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
    ->withMiddleware(function (Middleware $middleware): void {
        // Register custom role middleware aliases
        $middleware->alias([
            'owner' => \App\Http\Middleware\EnsureUserIsOwner::class,
            'member' => \App\Http\Middleware\EnsureUserIsMember::class,
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'banned' => \App\Http\Middleware\CheckIfBanned::class,
        ]);

        // Automatically check for banned users on every authenticated request
        $middleware->appendToGroup('auth', \App\Http\Middleware\CheckIfBanned::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
