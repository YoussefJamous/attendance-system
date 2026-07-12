<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $hr = User::factory()->create([
            'name' => 'HR User',
            'email' => 'hr@example.com',
            'password' => Hash::make('Password123!'),
        ]);
        $hr->assignRole(Role::HR->value);

        User::factory()->count(20)->employee()->create();

        $this->command?->info('Seeded login user: hr@example.com / Password123!');
        $this->command?->info('Seeded 20 employee users with Password123!');
    }
}
