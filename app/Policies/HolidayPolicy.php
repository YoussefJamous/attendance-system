<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Holiday;
use App\Models\User;

class HolidayPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::HOLIDAYS_VIEW->value);
    }

    public function view(User $user, Holiday $holiday): bool
    {
        return $user->can(Permission::HOLIDAYS_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::HOLIDAYS_CREATE->value);
    }

    public function update(User $user, Holiday $holiday): bool
    {
        return $user->can(Permission::HOLIDAYS_UPDATE->value);
    }

    public function delete(User $user, Holiday $holiday): bool
    {
        return $user->can(Permission::HOLIDAYS_DELETE->value);
    }
}
