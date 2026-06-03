<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\SetPasswordRequest;
use App\Jobs\SendEmailVerificationJob;
use App\Models\Member;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class AuthController extends BaseController
{
    /**
     * POST /api/v1/auth/register
     * Langkah 1 registrasi: data dasar + kirim email verifikasi.
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        $user = DB::transaction(function () use ($request) {
            $user = User::create([
                'name'              => $request->name,
                'email'             => $request->email,
                'phone'             => $request->phone,
                'bank_name'         => $request->bank_name,
                'bank_account_no'   => $request->bank_account_no,
                'bank_account_name' => $request->bank_account_name,
                'role'              => 'member',
                // password null sampai email diverifikasi
            ]);

            Member::create([
                'user_id'     => $user->id,
                'rental_name' => $request->rental_name,
                'status'      => 'pending_verification',
                'period_year' => now()->format('Y'),
            ]);

            return $user;
        });

        SendEmailVerificationJob::dispatch($user);

        return $this->success(
            ['email' => $user->email],
            'Pendaftaran berhasil. Silakan cek email Anda untuk melanjutkan.',
            201,
        );
    }

    /**
     * GET /api/v1/auth/verify-email/{user}?signature=...
     * Dipanggil dari link email. Verifikasi signature, set email_verified_at,
     * buat token set-password, lalu redirect ke frontend.
     */
    public function verifyEmail(Request $request, User $user): RedirectResponse
    {
        if (! $request->hasValidSignature()) {
            $frontendUrl = rtrim(config('app.frontend_url'), '/');
            return redirect("{$frontendUrl}/verifikasi?status=invalid");
        }

        if (! $user->email_verified_at) {
            $user->update(['email_verified_at' => now()]);
        }

        // Buat token set-password via password_reset_tokens (reuse tabel bawaan Laravel)
        $token = Str::random(64);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => Hash::make($token), 'created_at' => now()],
        );

        $frontendUrl = rtrim(config('app.frontend_url'), '/');
        $params = http_build_query([
            'email' => $user->email,
            'token' => $token,
        ]);

        return redirect("{$frontendUrl}/set-password?{$params}");
    }

    /**
     * POST /api/v1/auth/set-password
     * Set password setelah verifikasi email (menggunakan token dari email link).
     */
    public function setPassword(SetPasswordRequest $request): JsonResponse
    {
        $record = DB::table('password_reset_tokens')
            ->where('email', $request->email)
            ->first();

        if (! $record || ! Hash::check($request->token, $record->token)) {
            return $this->error('Token tidak valid atau sudah kadaluarsa.', 422);
        }

        // Token berlaku 24 jam (sama dengan link verifikasi)
        if (now()->diffInHours($record->created_at) > 24) {
            DB::table('password_reset_tokens')->where('email', $request->email)->delete();
            return $this->error('Token sudah kadaluarsa. Minta link verifikasi baru.', 422);
        }

        $user = User::where('email', $request->email)->firstOrFail();
        $user->update(['password' => $request->password]);

        DB::table('password_reset_tokens')->where('email', $request->email)->delete();

        return $this->success(null, 'Password berhasil dibuat. Silakan login.');
    }

    /**
     * POST /api/v1/auth/resend-verification
     * Kirim ulang email verifikasi jika user belum memverifikasi.
     */
    public function resendVerification(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email', 'exists:users,email']]);

        $user = User::where('email', $request->email)->firstOrFail();

        if ($user->email_verified_at) {
            return $this->error('Email sudah terverifikasi. Silakan login.', 422);
        }

        SendEmailVerificationJob::dispatch($user);

        return $this->success(null, 'Email verifikasi telah dikirim ulang.');
    }

    /**
     * POST /api/v1/auth/login
     */
    public function login(LoginRequest $request): JsonResponse
    {
        $user = User::where('email', $request->email)->first();

        if (! $user || ! $user->password || ! Hash::check($request->password, $user->password)) {
            return $this->error('Email atau password salah.', 401);
        }

        if (! $user->email_verified_at) {
            return $this->error('Email belum diverifikasi. Silakan cek kotak masuk email Anda.', 403);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return $this->success(
            $this->userPayload($user),
            'Login berhasil.',
        );
    }

    /**
     * POST /api/v1/auth/logout
     */
    public function logout(Request $request): JsonResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $this->success(null, 'Logout berhasil.');
    }

    /**
     * GET /api/v1/auth/me
     */
    public function me(Request $request): JsonResponse
    {
        return $this->success($this->userPayload($request->user()));
    }

    private function userPayload(User $user): array
    {
        $user->loadMissing('member');

        return [
            'id'                => $user->id,
            'name'              => $user->name,
            'email'             => $user->email,
            'role'              => $user->role,
            'phone'             => $user->phone,
            'bank_name'         => $user->bank_name,
            'bank_account_no'   => $user->bank_account_no,
            'bank_account_name' => $user->bank_account_name,
            'member'            => $user->member ? [
                'id'          => $user->member->id,
                'rental_name' => $user->member->rental_name,
                'status'      => $user->member->status->value,
                'period_year' => $user->member->period_year,
                'membership_no' => $user->member->membership_no,
            ] : null,
        ];
    }
}
