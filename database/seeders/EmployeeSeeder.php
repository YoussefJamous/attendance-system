<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;

class EmployeeSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(20)->create()->each(function (User $user) {
            $user->assignRole(Role::EMPLOYEE->value);

            Employee::factory()->for($user)->create();
        });

        $this->command?->info('Seeded 20 employee users with Password123!');
    }
}
