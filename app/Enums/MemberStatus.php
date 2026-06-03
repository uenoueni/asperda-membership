<?php

namespace App\Enums;

enum MemberStatus: string
{
    case PendingVerification = 'pending_verification';
    case WaitingSurvey       = 'waiting_survey';
    case Active              = 'active';
    case Rejected            = 'rejected';
    case Expired             = 'expired';
}
