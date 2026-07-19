<?php

namespace Database\Seeders;

use App\Enums\Permission;
use App\Enums\Role;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role as SpatieRole;

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
            Permission::EMPLOYEES_RSTORE->value,
            Permission::DEPARTMENTS_VIEW->value,
            Permission::DEPARTMENTS_CREATE->value,
            Permission::DEPARTMENTS_UPDATE->value,
            Permission::DEPARTMENTS_DELETE->value,
            Permission::SHIFTS_VIEW->value,
            Permission::SHIFTS_CREATE->value,
            Permission::SHIFTS_UPDATE->value,
            Permission::SHIFTS_DELETE->value,
            Permission::HOLIDAYS_VIEW->value,
            Permission::HOLIDAYS_CREATE->value,
            Permission::HOLIDAYS_UPDATE->value,
            Permission::HOLIDAYS_DELETE->value,
            Permission::SYSTEM_CONFIGURATION_VIEW->value,
            Permission::SYSTEM_CONFIGURATION_MANAGE->value,
            Permission::ATTENDANCE_CORRECTIONS_VIEW->value,
            Permission::ATTENDANCE_CORRECTIONS_MANAGE->value,
        ]);

        $employee->syncPermissions([
            Permission::PROFILE_VIEW->value,
            Permission::PROFILE_UPDATE->value,
            Permission::DEPARTMENTS_VIEW->value,
            Permission::ATTENDANCE_CLOCK_IN->value,
            Permission::ATTENDANCE_CLOCK_OUT->value,
            Permission::ATTENDANCE_CORRECTIONS_VIEW->value,
            Permission::ATTENDANCE_CORRECTIONS_CREATE->value,
        ]);
    }
}
