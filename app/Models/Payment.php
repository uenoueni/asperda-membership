<?php

namespace App\Models;

use App\Enums\PaymentStatus;
use App\Enums\PaymentType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Payment extends Model
{
    protected $fillable = [
        'member_id',
        'midtrans_order_id',
        'midtrans_transaction_id',
        'type',
        'period_year',
        'amount',
        'status',
        'paid_at',
        'payment_channel',
        'midtrans_raw_response',
    ];

    protected $casts = [
        'type'                  => PaymentType::class,
        'status'                => PaymentStatus::class,
        'amount'                => 'decimal:2',
        'paid_at'               => 'datetime',
        'midtrans_raw_response' => 'array',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }

    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class);
    }
}
