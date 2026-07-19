<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AttendanceStatus: string
{
    use HasValues;

    case INCOMPLETE = 'incomplete';
    case WAITING_FOR_APPROVAL = 'waiting_for_approval';
    case COMPLETED = 'completed';
}
