<?php

namespace App\Enums;

enum RefundStatus: string
{
    case Queued     = 'queued';
    case Processing = 'processing';
    case Completed  = 'completed';
    case Cancelled  = 'cancelled';
}
