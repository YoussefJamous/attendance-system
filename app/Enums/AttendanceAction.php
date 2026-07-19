<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AttendanceAction: string
{
    use HasValues;

    case CLOCK_IN = 'clock_in';
    case CLOCK_OUT = 'clock_out';
}
