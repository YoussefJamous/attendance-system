<?php

namespace Database\Seeders;

use App\Models\Shift;
use Illuminate\Database\Seeder;

class ShiftSeeder extends Seeder
{
    public function run(): void
    {
        $shift = Shift::firstOrCreate(
            ['name' => 'Morning Shift'],
            [
                'description' => 'Standard weekday shift.',
                'is_active' => true,
            ]
        );

        foreach (['monday', 'tuesday', 'wednesday', 'thursday'] as $dayOfWeek) {
            $shift->days()->firstOrCreate(
                ['day_of_week' => $dayOfWeek],
                [
                    'work_start_time' => '09:00',
                    'work_end_time' => '18:00',
                    'ends_next_day' => false,
                    'break_duration_minutes' => 60,
                ]
            );
        }

        $shift->days()->firstOrCreate(
            ['day_of_week' => 'friday'],
            [
                'work_start_time' => '09:00',
                'work_end_time' => '18:00',
                'ends_next_day' => false,
                'break_duration_minutes' => 90,
            ]
        );

        $this->command?->info('Seeded Morning Shift with weekday schedules.');
    }
}
