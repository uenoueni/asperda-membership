<?php

namespace App\Enums;

enum StarterkitDistributionStatus: string
{
    case Pending     = 'pending';
    case Distributed = 'distributed';
    case Confirmed   = 'confirmed';
}
