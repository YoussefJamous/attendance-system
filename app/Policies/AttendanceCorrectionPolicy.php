<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\AttendanceCorrection;
use App\Models\User;

class AttendanceCorrectionPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::ATTENDANCE_CORRECTIONS_VIEW->value)
            || $user->can(Permission::ATTENDANCE_CORRECTIONS_MANAGE->value);
    }

    public function view(User $user, AttendanceCorrection $correction): bool
    {
        return $user->can(Permission::ATTENDANCE_CORRECTIONS_MANAGE->value)
            || ($user->can(Permission::ATTENDANCE_CORRECTIONS_VIEW->value)
                && $correction->attendance->employee->user_id === $user->id);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::ATTENDANCE_CORRECTIONS_CREATE->value);
    }

    public function approve(User $user, AttendanceCorrection $correction): bool
    {
        return $user->can(Permission::ATTENDANCE_CORRECTIONS_MANAGE->value);
    }

    public function reject(User $user, AttendanceCorrection $correction): bool
    {
        return $user->can(Permission::ATTENDANCE_CORRECTIONS_MANAGE->value);
    }
}
