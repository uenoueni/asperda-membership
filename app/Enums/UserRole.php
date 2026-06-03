<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case DPP        = 'dpp';
    case DPD        = 'dpd';
    case DPC        = 'dpc';
    case Member     = 'member';
}
