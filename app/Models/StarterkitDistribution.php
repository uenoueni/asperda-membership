<?php

namespace App\Models;

use App\Enums\StarterkitDistributionStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StarterkitDistribution extends Model
{
    protected $fillable = [
        'member_id',
        'payment_id',
        'item_id',
        'period_year',
        'status',
        'distributed_at',
        'confirmed_at',
        'distributed_by',
        'notes',
    ];

    protected $casts = [
        'status'         => StarterkitDistributionStatus::class,
        'distributed_at' => 'datetime',
        'confirmed_at'   => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(StarterkitItem::class, 'item_id');
    }

    public function distributedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'distributed_by');
    }
}
