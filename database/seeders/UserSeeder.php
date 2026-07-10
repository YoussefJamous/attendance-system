<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hr = User::factory()->create([
            'name' => 'HR User',
            'email' => 'hr@example.com',
        ]);

        $hr->assignRole(Role::HR->value);

        $this->command?->info('Seeded login user: hr@example.com / Password123!');
    }
}
