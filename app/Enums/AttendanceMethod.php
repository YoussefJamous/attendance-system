<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum AttendanceMethod: string
{
    use HasValues;

    case GPS = 'gps';
    case OFFICE_WIFI = 'office_wifi';
}
