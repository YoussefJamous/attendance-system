<?php

namespace App\Console\Commands;

use App\Services\AttendanceService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Throwable;

class FinalizeAttendanceCommand extends Command
{
    protected $signature = 'attendance:finalize {date? : Attendance date in YYYY-MM-DD format}';

    protected $description = 'Finalize in-progress attendance records for a day.';

    public function handle(AttendanceService $attendanceService): int
    {
        try {
            $date = $this->argument('date')
                ? Carbon::createFromFormat('Y-m-d', $this->argument('date'))
                : today();
        } catch (Throwable) {
            $this->error('The date must use the YYYY-MM-DD format.');

            return self::FAILURE;
        }

        if ($date->format('Y-m-d') !== $this->argument('date') && $this->argument('date')) {
            $this->error('The date must use the YYYY-MM-DD format.');

            return self::FAILURE;
        }

        $result = $attendanceService->finalizeDay($date);

        $this->info("Finalized {$result['finalized']} attendance record(s) for {$result['date']}.");

        return self::SUCCESS;
    }
}
