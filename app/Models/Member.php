<?php

namespace App\Models;

use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    protected $fillable = [
        'user_id',
        'membership_no',
        'rental_name',
        'status',
        'registered_at',
        'expires_at',
        'period_year',
    ];

    protected $casts = [
        'status'        => MemberStatus::class,
        'registered_at' => 'date',
        'expires_at'    => 'date',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function primaryBranch(): HasOne
    {
        return $this->hasOne(Branch::class)->where('is_primary', true);
    }

    public function surveyAssignments(): HasMany
    {
        return $this->hasMany(SurveyAssignment::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function certificates(): HasMany
    {
        return $this->hasMany(Certificate::class);
    }

    public function starterkitDistributions(): HasMany
    {
        return $this->hasMany(StarterkitDistribution::class);
    }
}
