<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Attendance;
use App\Models\User;

class AttendancePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ATTENDANCE_VIEW->value)
            || $user->can(Permission::ATTENDANCE_MANAGE->value);
    }

    public function view(User $user, Attendance $attendance): bool
    {
        return $user->can(Permission::ATTENDANCE_MANAGE->value)
            || ($user->can(Permission::ATTENDANCE_VIEW->value)
                && $attendance->employee->user_id === $user->id);
    }

    public function record(User $user): bool
    {
        return $user->can(Permission::ATTENDANCE_RECORD->value);
    }
}
