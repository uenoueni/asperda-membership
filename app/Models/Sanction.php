<?php

namespace App\Models;

use App\Enums\SanctionSeverity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Sanction extends Model
{
    protected $fillable = [
        'survey_assignment_id',
        'issued_to',
        'issued_by',
        'reason',
        'severity',
        'notes',
    ];

    protected $casts = [
        'severity' => SanctionSeverity::class,
    ];

    public function surveyAssignment(): BelongsTo
    {
        return $this->belongsTo(SurveyAssignment::class);
    }

    public function issuedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_to');
    }

    public function issuedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'issued_by');
    }
}
