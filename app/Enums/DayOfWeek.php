<?php

namespace App\Enums;

use App\Enums\Concerns\HasValues;

enum DayOfWeek: string
{
    use HasValues;

    case MONDAY = 'monday';
    case TUESDAY = 'tuesday';
    case WEDNESDAY = 'wednesday';
    case THURSDAY = 'thursday';
    case FRIDAY = 'friday';
    case SATURDAY = 'saturday';
    case SUNDAY = 'sunday';
}
