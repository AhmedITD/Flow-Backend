<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Configure Authenticate middleware to not redirect for API routes
        Authenticate::redirectUsing(function ($request) {
            if ($request->is('api/*')) {
                // Return false (no redirect) for API routes
                return false;
            }
        });

        // Register middleware aliases
        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON for all API requests
        $exceptions->shouldRenderJsonWhen(function ($request, Throwable $e) {
            if ($request->is('api/*')) {
                return true;
            }
            
            return $request->expectsJson();
        });

        // Handle unauthenticated exceptions for API
        // $exceptions->render(function (AuthenticationException $e, $request) {
        //     if ($request->is('api/*') || $request->expectsJson()) {
        //         return response()->json([
        //             'error' => 'Unauthenticated',
        //             'message' => $e->getMessage() ?: 'Authentication required'
        //         ], 401);
        //     }
        // });
    })->create();
