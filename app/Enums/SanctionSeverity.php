<?php

namespace App\Enums;

enum SanctionSeverity: string
{
    case Warning     = 'warning';
    case Suspension  = 'suspension';
    case Termination = 'termination';
}
