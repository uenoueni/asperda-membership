<?php

namespace App\Enums;

enum SurveyStatus: string
{
    case Pending   = 'pending';
    case Accepted  = 'accepted';
    case Approved  = 'approved';
    case Rejected  = 'rejected';
    case Escalated = 'escalated';
}
