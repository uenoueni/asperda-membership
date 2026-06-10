<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\MemberController;
use App\Http\Controllers\Api\V1\PaymentController;
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

    // ─── Public: Payment Webhook (Midtrans) ────────────────────────────────────
    Route::post('/payment/webhook', [PaymentController::class, 'webhook']);

    // ─── Authenticated routes ──────────────────────────────────────────────────
    Route::middleware(['auth:sanctum'])->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });

        // ─── Member ───────────────────────────────────────────────────────────
        Route::prefix('member')->group(function () {
            Route::get('/', [MemberController::class, 'show']);
            Route::put('/', [MemberController::class, 'update']);
            Route::post('/complete-profile', [MemberController::class, 'completeProfile']);
        });

        // ─── Payment ──────────────────────────────────────────────────────────
        Route::prefix('payment')->group(function () {
            Route::post('/create', [PaymentController::class, 'create']);
            Route::get('/{payment}/status', [PaymentController::class, 'status'])
                ->name('payment.status');
        });

        // ─── Certificate download (stub — implementasi penuh Tahap 3) ────────
        Route::get('/certificate/{certificate}/download', function ($certificate) {
            return response()->json(['success' => false, 'message' => 'Belum tersedia.'], 501);
        })->name('certificate.download');

        // Modul berikutnya: Survey, Certificate, Refund, Starterkit, Admin
    });
});
