<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use App\Models\AppSetting;
use App\Models\Member;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    public function createSnapToken(Member $member, string $type): array
    {
        $amount = $type === PaymentType::Registration->value
            ? (int) AppSetting::get('registration_fee', 500000)
            : (int) AppSetting::get('renewal_fee', 300000);

        $payment = Payment::create([
            'member_id'         => $member->id,
            'midtrans_order_id' => 'ASPERDA-' . $member->id . '-' . time(),
            'type'              => $type,
            'period_year'       => now()->year,
            'amount'            => $amount,
            'status'            => PaymentStatus::Pending,
        ]);

        $params = [
            'transaction_details' => [
                'order_id'     => $payment->midtrans_order_id,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $member->user->name,
                'email'      => $member->user->email,
                'phone'      => $member->user->phone,
            ],
        ];

        $snapToken = \Midtrans\Snap::getSnapToken($params);

        return [
            'payment_id' => $payment->id,
            'snap_token' => $snapToken,
            'order_id'   => $payment->midtrans_order_id,
            'amount'     => $amount,
        ];
    }

    public function handlePaidPayment(Payment $payment): void
    {
        $member = $payment->member;

        if ($payment->type === PaymentType::Registration) {
            $member->update([
                'status'        => MemberStatus::WaitingSurvey,
                'registered_at' => now()->toDateString(),
            ]);
            Log::info("Member #{$member->id} masuk antrian survey setelah payment #{$payment->id}.");
        } elseif ($payment->type === PaymentType::Renewal) {
            $durationMonths = (int) AppSetting::get('membership_duration_months', 12);
            $member->update([
                'status'      => MemberStatus::Active,
                'expires_at'  => ($member->expires_at ?? now())->addMonths($durationMonths),
                'period_year' => now()->year,
            ]);
            Log::info("Member #{$member->id} diperpanjang setelah payment #{$payment->id}.");
        }
    }

    /**
     * Cek status terbaru ke Midtrans untuk payment yang masih pending.
     * Dipakai sebagai fallback saat webhook belum/tidak sampai (mis. dev lokal).
     */
    public function syncStatusFromMidtrans(Payment $payment): Payment
    {
        if ($payment->status !== PaymentStatus::Pending) {
            return $payment;
        }

        try {
            $response = \Midtrans\Transaction::status($payment->midtrans_order_id);
        } catch (\Throwable $e) {
            Log::warning("Cek status Midtrans gagal untuk order {$payment->midtrans_order_id}: {$e->getMessage()}");
            return $payment;
        }

        $transactionId = $response->transaction_id ?? null;
        if (!$transactionId || $payment->midtrans_transaction_id === $transactionId) {
            return $payment;
        }

        $newStatus = $this->mapMidtransStatus($response->transaction_status);

        DB::transaction(function () use ($payment, $response, $newStatus) {
            $payment->update([
                'midtrans_transaction_id' => $response->transaction_id,
                'status'                  => $newStatus,
                'paid_at'                 => in_array($response->transaction_status, ['settlement', 'capture'])
                    ? now() : null,
                'payment_channel'         => $response->payment_type ?? null,
                'midtrans_raw_response'   => (array) $response,
            ]);

            if ($payment->fresh()->status === PaymentStatus::Paid) {
                $this->handlePaidPayment($payment->fresh());
            }
        });

        return $payment->fresh();
    }

    public function mapMidtransStatus(string $status): PaymentStatus
    {
        return match ($status) {
            'settlement', 'capture' => PaymentStatus::Paid,
            'pending'               => PaymentStatus::Pending,
            'deny', 'cancel'        => PaymentStatus::Failed,
            'expire'                => PaymentStatus::Expired,
            default                 => PaymentStatus::Pending,
        };
    }
}
