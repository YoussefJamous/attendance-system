<?php

namespace Database\Factories;

use App\Enums\EmployeeStatus;
use App\Enums\Gender;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'hire_date' => fake()->dateTimeBetween('2024-01-01', 'now'),
            'identity_document_path' => null,
            'status' => EmployeeStatus::ACTIVE,
            'gender' => fake()->randomElement(Gender::cases()),
        ];
    }
}
