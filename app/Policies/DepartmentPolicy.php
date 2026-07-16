<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Department;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class DepartmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::DEPARTMENTS_VIEW->value);
    }

    public function view(User $user, Department $department): bool
    {
        return $user->can(Permission::DEPARTMENTS_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::DEPARTMENTS_CREATE->value);
    }

    public function update(User $user, Department $department): bool
    {
        return $user->can(Permission::DEPARTMENTS_UPDATE->value);
    }

    public function destroy(User $user, Department $department): bool
    {
        return $user->can(Permission::DEPARTMENTS_DELETE->value);
    }
}
