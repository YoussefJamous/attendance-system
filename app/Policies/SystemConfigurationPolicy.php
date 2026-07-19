<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\User;

class SystemConfigurationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can(Permission::SYSTEM_CONFIGURATION_VIEW->value);
    }

    public function manage(User $user): bool
    {
        return $user->can(Permission::SYSTEM_CONFIGURATION_MANAGE->value);
    }
}
