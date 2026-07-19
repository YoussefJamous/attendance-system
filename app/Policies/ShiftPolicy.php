<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Shift;
use App\Models\User;

class ShiftPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::SHIFTS_VIEW->value);
    }

    public function view(User $user, Shift $shift): bool
    {
        return $user->can(Permission::SHIFTS_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::SHIFTS_CREATE->value);
    }

    public function update(User $user, Shift $shift): bool
    {
        return $user->can(Permission::SHIFTS_UPDATE->value);
    }

    public function delete(User $user, Shift $shift): bool
    {
        return $user->can(Permission::SHIFTS_DELETE->value);
    }
}
