<?php

use App\Http\Middleware\EnsurePasswordChanged;
use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Inertia\Inertia;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);

        $middleware->alias([
            'must.change.password' => EnsurePasswordChanged::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->respond(function ($response, Throwable $exception, Request $request) {
            if ($response->getStatusCode() === 403) {
                if (app()->environment('testing')) {
                    return $response;
                }

                if ($request->header('X-Inertia')) {
                    return Inertia::render('Errors/403', [
                        'status' => 403,
                        'message' => $exception->getMessage() ?: 'Anda tidak memiliki hak akses yang memadai untuk membuka halaman atau fitur ini.',
                    ])->toResponse($request)->setStatusCode(403);
                }

                if (! $request->expectsJson() && $request->isMethod('GET')) {
                    return response()->view('errors.403', [
                        'exception' => $exception,
                        'message' => $exception->getMessage() ?: 'Anda tidak memiliki hak akses yang memadai untuk membuka halaman atau fitur ini.',
                    ], 403);
                }
            }

            return $response;
        });
    })->create();
