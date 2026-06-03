<?php

namespace App\Models;

use App\Enums\RefundStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Refund extends Model
{
    protected $fillable = [
        'payment_id',
        'member_id',
        'survey_assignment_id',
        'rejection_reason',
        'rejected_by',
        'original_amount',
        'refund_amount',
        'bank_name',
        'bank_account_no',
        'bank_account_name',
        'planned_refund_date',
        'actual_refund_date',
        'status',
        'processed_by',
        'notes',
    ];

    protected $casts = [
        'status'              => RefundStatus::class,
        'original_amount'     => 'decimal:2',
        'refund_amount'       => 'decimal:2',
        'planned_refund_date' => 'date',
        'actual_refund_date'  => 'date',
    ];

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function surveyAssignment(): BelongsTo
    {
        return $this->belongsTo(SurveyAssignment::class);
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function processedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
