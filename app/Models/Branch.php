<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Branch extends Model
{
    protected $fillable = [
        'member_id',
        'branch_name',
        'province_code',
        'city_code',
        'district_code',
        'address',
        'unit_count',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'unit_count' => 'integer',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
}
