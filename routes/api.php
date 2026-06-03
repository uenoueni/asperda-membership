<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\WilayahController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes (v1)
|--------------------------------------------------------------------------
*/

Route::prefix('v1')->group(function () {

    // Health check
    Route::get('/ping', fn () => response()->json([
        'success' => true,
        'message' => 'pong',
        'data'    => ['app' => config('app.name'), 'time' => now()->toIso8601String()],
    ]));

    // ─── Public: Auth ───────────────────────────────────────────────────────────
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/resend-verification', [AuthController::class, 'resendVerification']);
        Route::post('/set-password', [AuthController::class, 'setPassword']);

        // Named route: dipanggil dari email link (signed URL)
        Route::get('/verify-email/{user}', [AuthController::class, 'verifyEmail'])
            ->name('auth.verify-email');
    });

    // ─── Public: Wilayah (lazy-load dropdown) ──────────────────────────────────
    Route::prefix('wilayah')->group(function () {
        Route::get('/provinsi', [WilayahController::class, 'provinsi']);
        Route::get('/kota/{provinceCode}', [WilayahController::class, 'kota']);
        Route::get('/kecamatan/{cityCode}', [WilayahController::class, 'kecamatan']);
    });

    // ─── Authenticated routes ──────────────────────────────────────────────────
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        // Modul berikutnya ditambahkan di sini saat dikerjakan:
        // Member, Payment, Survey, Certificate, Refund, Starterkit, Admin
    });
});
