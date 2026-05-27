<?php

use App\Http\Middleware\AuditLogMiddleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\SecurityHeaders;
use App\Models\ErrorLog;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Global web middleware
        $middleware->web(append: [
            SecurityHeaders::class,
            AuditLogMiddleware::class,
        ]);

        // Named middleware aliases
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Log all unhandled exceptions to error_logs table
        $exceptions->report(function (\Throwable $e) {
            if ($e instanceof NotFoundHttpException || $e instanceof AccessDeniedHttpException) {
                return;
            }

            try {
                ErrorLog::create([
                    'severity'    => 'critical',
                    'message'     => $e->getMessage() ?: get_class($e),
                    'stack_trace' => $e->getTraceAsString(),
                    'source'      => get_class($e),
                    'context'     => [
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                    ],
                    'created_at'  => now(),
                ]);
            } catch (\Throwable) {
                // Prevent recursive exception logging from crashing the app
            }
        });

        // Custom 403 response
        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if (!$request->expectsJson()) {
                return response()->view('errors.403', ['exception' => $e], 403);
            }
        });

        // Custom 404 response
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if (!$request->expectsJson()) {
                return response()->view('errors.404', ['exception' => $e], 404);
            }
        });
    })->create();
