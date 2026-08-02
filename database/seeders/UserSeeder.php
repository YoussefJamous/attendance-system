<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hr = User::factory()->create([
            'email' => 'hr@example.com',
            'password' => Hash::make('Password123!'),
        ]);
        $hr->assignRole(Role::HR->value);

        $employeeUser = User::factory()->create([
            'name' => 'Test Employee',
            'email' => 'employee@example.com',
            'password' => Hash::make('Password123!'),
        ]);
        $employeeUser->assignRole(Role::EMPLOYEE->value);

        Employee::factory()->for($employeeUser)->create([
            'first_name' => 'Test',
            'last_name' => 'Employee',
            'department_id' => Department::query()->value('id'),
            'shift_id' => Shift::query()->where('name', 'Morning Shift')->value('id'),
        ]);

        $this->command?->info('Seeded login user: hr@example.com / Password123!');
        $this->command?->info('Seeded login user: employee@example.com / Password123!');
    }
}
