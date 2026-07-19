<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class AttendancePolicy
{
    public function clockIn(User $user): bool
    {
        return $user->can(Permission::ATTENDANCE_CLOCK_IN->value);
    }

    public function clockOut(User $user): bool
    {
        return $user->can(Permission::ATTENDANCE_CLOCK_OUT->value);
    }
}
