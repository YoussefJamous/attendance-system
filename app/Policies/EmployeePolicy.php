<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class EmployeePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::EMPLOYEES_VIEW->value);
    }

    public function view(User $user, Employee $employee): bool
    {
        return $user->can(Permission::EMPLOYEES_VIEW->value);
    }

    public function create(User $user): bool
    {
        return $user->can(Permission::EMPLOYEES_CREATE->value);
    }

    public function update(User $user, Employee $employee): bool
    {
        return $user->can(Permission::EMPLOYEES_UPDATE->value);
    }

    public function delete(User $user, Employee $employee): bool
    {
        return $user->can(Permission::EMPLOYEES_DELETE->value);
    }

    public function restore(User $user, Employee $employee): bool
    {
        return $user->can(Permission::EMPLOYEES_RSTORE->value);
    }

    public function forceDelete(User $user, Employee $employee): bool
    {
        return false;
    }
}
