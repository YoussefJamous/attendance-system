<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        $departmentIds = Department::pluck('id');
        $shiftId = Shift::where('name', 'Morning Shift')->value('id');

        User::factory()->count(20)->create()->each(function (User $user) use ($departmentIds, $shiftId) {
            $user->assignRole(Role::EMPLOYEE->value);
            Employee::factory()->for($user)->create([
                'department_id' => $departmentIds->random(),
                'shift_id' => $shiftId,
            ]);
        });

        $this->command?->info('Seeded 20 employee users with Password123!');
    }
}
