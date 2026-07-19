<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AttendanceCorrectionStatus: string
{
    use HasValues;

    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
}
