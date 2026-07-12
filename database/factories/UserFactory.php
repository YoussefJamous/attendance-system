<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserFactory extends Factory
{

    protected $model = User::class;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => Hash::make(Str::random(10)),
            // 'remember_token' => Str::random(10),
        ];
    }

    public function employee(): static
    {
        return $this->afterCreating(function (User $user) {
            $user->assignRole(Role::EMPLOYEE->value);
        });
    }
}
