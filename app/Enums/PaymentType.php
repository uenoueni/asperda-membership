<?php

namespace App\Enums;

enum PaymentType: string
{
    case Registration = 'registration';
    case Renewal      = 'renewal';
}
