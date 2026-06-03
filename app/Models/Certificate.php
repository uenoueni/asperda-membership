<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    protected $fillable = [
        'member_id',
        'payment_id',
        'cert_number',
        'period_year',
        'valid_from',
        'valid_until',
        'file_path',
        'generated_at',
    ];

    protected $casts = [
        'valid_from'   => 'date',
        'valid_until'  => 'date',
        'generated_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }
}
