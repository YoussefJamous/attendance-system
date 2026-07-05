<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'employee@example.com'],
            [
                'name' => 'Youssef Jamous',
                'password' => 'Password123!',
            ],
        );

        $this->command?->info('Seeded login user: employee@example.com / Password123!');
    }
}
