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
        // Sanctum SPA: aktifkan cookie-based stateful auth untuk grup 'api'.
        $middleware->statefulApi();

        // Alias middleware RBAC (dipakai di routes/api.php: middleware('role:dpc,dpd,...')).
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Format semua exception API ke envelope standar { success, message, errors? }.
        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->is('api/*') && ! $request->expectsJson()) {
                return null; // biarkan handler default untuk request web
            }

            return match (true) {
                $e instanceof \Illuminate\Validation\ValidationException => response()->json([
                    'success' => false,
                    'message' => 'Data tidak valid.',
                    'errors'  => $e->errors(),
                ], 422),
                $e instanceof \Illuminate\Auth\AuthenticationException => response()->json([
                    'success' => false,
                    'message' => 'Tidak terautentikasi.',
                ], 401),
                $e instanceof \Illuminate\Auth\Access\AuthorizationException,
                $e instanceof \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException => response()->json([
                    'success' => false,
                    'message' => 'Anda tidak memiliki akses ke sumber daya ini.',
                ], 403),
                $e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException,
                $e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException => response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan.',
                ], 404),
                default => null,
            };
        });
    })->create();
