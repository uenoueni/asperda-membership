<?php

namespace App\Models;

use App\Enums\SurveyLevel;
use App\Enums\SurveyStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class SurveyAssignment extends Model
{
    protected $fillable = [
        'member_id',
        'assigned_to',
        'organizational_unit_id',
        'level',
        'status',
        'rejection_reason',
        'deadline',
        'accepted_at',
        'decided_at',
        'escalated_from',
    ];

    protected $casts = [
        'level'       => SurveyLevel::class,
        'status'      => SurveyStatus::class,
        'deadline'    => 'datetime',
        'accepted_at' => 'datetime',
        'decided_at'  => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function organizationalUnit(): BelongsTo
    {
        return $this->belongsTo(OrganizationalUnit::class);
    }

    public function escalatedFrom(): BelongsTo
    {
        return $this->belongsTo(SurveyAssignment::class, 'escalated_from');
    }

    public function escalations(): HasMany
    {
        return $this->hasMany(SurveyAssignment::class, 'escalated_from');
    }

    public function sanctions(): HasMany
    {
        return $this->hasMany(Sanction::class);
    }

    public function refund(): HasOne
    {
        return $this->hasOne(Refund::class);
    }
}
