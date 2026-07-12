<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;
use Spatie\Permission\Models\Permission as SpatiePermission;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hr = SpatieRole::findByName(Role::HR->value);
        $employee = SpatieRole::findByName(Role::EMPLOYEE->value); 

        $hr->syncPermissions([
            Permission::EMPLOYEES_VIEW->value,
            Permission::EMPLOYEES_CREATE->value,
            Permission::EMPLOYEES_UPDATE->value,
            Permission::EMPLOYEES_DELETE->value,
        ]);

        $employee->syncPermissions([
            Permission::PROFILE_VIEW->value,
            Permission::PROFILE_UPDATE->value,
        ]);
    }
}
