<?php

namespace Database\Seeders;

use App\Enums\AttendanceMethod;
use App\Models\SystemConfiguration;
use Illuminate\Database\Seeder;

class SystemConfigurationSeeder extends Seeder
{
    public function run(): void
    {
        SystemConfiguration::query()->firstOrCreate([], [
            'attendance_method' => AttendanceMethod::OFFICE_WIFI,
            'minimum_action_interval_minutes' => 1,
            'grace_minutes' => 0,
        ]);

        $this->command?->info('Seeded Version 1 attendance configuration using Office WiFi.');
    }
}
