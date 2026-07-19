<?php

namespace Database\Seeders;

use App\Models\Holiday;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        Holiday::firstOrCreate(
            [
                'name' => 'New Year\'s Day',
                'start_date' => '2026-01-01',
                'end_date' => '2026-01-01',
            ],
            ['description' => 'Public holiday.']
        );

        $this->command?->info('Seeded New Year\'s Day holiday.');
    }
}
