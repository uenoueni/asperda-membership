<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Requests\Payment\CreatePaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends BaseController
{
    public function __construct(private PaymentService $paymentService) {}

    public function create(CreatePaymentRequest $request): JsonResponse
    {
        $member = Auth::user()->member;

        if (!$member) {
            return $this->error('Data member tidak ditemukan.', 404);
        }

        if (!$member->primaryBranch) {
            return $this->error('Profil belum lengkap. Isi data cabang terlebih dahulu.', 422);
        }

        try {
            $result = $this->paymentService->createSnapToken($member, $request->validated()['type']);
        } catch (\Throwable $e) {
            Log::error('Midtrans createSnapToken gagal: ' . $e->getMessage());
            return $this->error('Gagal menginisiasi pembayaran. Silakan coba lagi.', 500);
        }

        return $this->success($result, 'Token pembayaran berhasil dibuat.', 201);
    }

    public function status(Payment $payment): JsonResponse
    {
        if ($payment->member->user_id !== Auth::id()) {
            return $this->error('Akses tidak diizinkan.', 403);
        }

        $payment = $this->paymentService->syncStatusFromMidtrans($payment);

        return $this->success(new PaymentResource($payment));
    }

    public function webhook(Request $request): JsonResponse
    {
        try {
            $notification = new \Midtrans\Notification();

            $expected = hash('sha512',
                $notification->order_id .
                $notification->status_code .
                $notification->gross_amount .
                config('midtrans.server_key')
            );

            if ($notification->signature_key !== $expected) {
                Log::warning('Midtrans webhook: signature tidak cocok untuk order ' . $notification->order_id);
                return response()->json(['success' => false], 403);
            }

            $payment = Payment::where('midtrans_order_id', $notification->order_id)->first();
            if (!$payment) {
                return response()->json(['success' => true]);
            }

            if ($payment->midtrans_transaction_id === $notification->transaction_id) {
                return response()->json(['success' => true]);
            }

            $newStatus = $this->paymentService->mapMidtransStatus($notification->transaction_status);

            DB::transaction(function () use ($payment, $notification, $newStatus) {
                $payment->update([
                    'midtrans_transaction_id' => $notification->transaction_id,
                    'status'                  => $newStatus,
                    'paid_at'                 => in_array($notification->transaction_status, ['settlement', 'capture'])
                        ? now() : null,
                    'payment_channel'         => $notification->payment_type,
                    'midtrans_raw_response'   => (array) $notification->getResponse(),
                ]);

                if ($payment->fresh()->status->value === 'paid') {
                    $this->paymentService->handlePaidPayment($payment->fresh());
                }
            });

        } catch (\Throwable $e) {
            Log::error('Midtrans webhook error: ' . $e->getMessage());
        }

        return response()->json(['success' => true]);
    }
}
