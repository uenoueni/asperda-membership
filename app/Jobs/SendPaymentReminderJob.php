<?php

namespace App\Jobs;

use App\Enums\PaymentStatus;
use App\Mail\PaymentReminderMail;
use App\Models\Payment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPaymentReminderJob implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    public function handle(): void
    {
        $pending = Payment::query()
            ->where('status', PaymentStatus::Pending)
            ->where('created_at', '<=', now()->subDay())
            ->with('member.user')
            ->get();

        foreach ($pending as $payment) {
            try {
                Mail::to($payment->member->user->email)
                    ->send(new PaymentReminderMail($payment));
            } catch (\Throwable $e) {
                Log::error("Gagal kirim reminder payment #{$payment->id}: {$e->getMessage()}");
            }
        }

        Log::info("SendPaymentReminderJob: {$pending->count()} reminder terkirim.");
    }
}
