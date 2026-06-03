<?php

namespace App\Jobs;

use App\Mail\VerificationMail;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;

class SendEmailVerificationJob implements ShouldQueue
{
    use Queueable, InteractsWithQueue, SerializesModels;

    public int $tries = 3;
    public int $backoff = 60;

    public function __construct(private readonly User $user) {}

    public function handle(): void
    {
        $signedUrl = URL::temporarySignedRoute(
            'auth.verify-email',
            now()->addHours(24),
            ['user' => $this->user->id],
        );

        Mail::to($this->user->email)->send(new VerificationMail($this->user, $signedUrl));
    }

    public function failed(\Throwable $e): void
    {
        \Log::error('SendEmailVerificationJob gagal', [
            'user_id' => $this->user->id,
            'error'   => $e->getMessage(),
        ]);
    }
}
