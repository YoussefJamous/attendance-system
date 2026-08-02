<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class AttendancePolicy
{
    public function record(User $user): bool
    {
        return $user->can(Permission::ATTENDANCE_RECORD->value);
    }
}
