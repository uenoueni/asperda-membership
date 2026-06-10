<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'payment_id'      => $this->id,
            'type'            => $this->type->value,
            'status'          => $this->status->value,
            'amount'          => $this->amount,
            'order_id'        => $this->midtrans_order_id,
            'paid_at'         => $this->paid_at?->toIso8601String(),
            'payment_channel' => $this->payment_channel,
            'period_year'     => $this->period_year,
        ];
    }
}
