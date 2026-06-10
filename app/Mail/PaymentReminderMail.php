<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public readonly \App\Models\Payment $payment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pengingat: Selesaikan Pembayaran Pendaftaran ASPERDA',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
            with: [
                'memberName'  => $this->payment->member->user->name,
                'rentalName'  => $this->payment->member->user->rental_name,
                'amount'      => number_format($this->payment->amount, 0, ',', '.'),
                'orderId'     => $this->payment->midtrans_order_id,
                'paymentUrl'  => config('app.url') . '/bayar',
                'createdAt'   => $this->payment->created_at->format('d F Y'),
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
