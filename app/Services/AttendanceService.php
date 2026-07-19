<?php

namespace App\Services;

use App\Enums\AttendanceAction;
use App\Enums\AttendanceStatus;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceService
{
    public function clockIn(User $user, UploadedFile $image): Attendance
    {
        return DB::transaction(function () use ($user, $image) {
            $employee = $this->attendanceEmployee($user);
            $now = now();

            $attendance = Attendance::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $now->toDateString())
                ->first();

            if (! $attendance) {
                $attendance = Attendance::create([
                    'employee_id' => $employee->id,
                    'attendance_date' => $now->toDateString(),
                    'status' => AttendanceStatus::INCOMPLETE,
                ]);
            }

            $this->addLog($attendance, AttendanceAction::CLOCK_IN, $image);

            return $attendance->load('logs');
        });
    }

    public function clockOut(User $user, UploadedFile $image): Attendance
    {
        return DB::transaction(function () use ($user, $image) {
            $employee = $this->attendanceEmployee($user);
            $now = now();
            $attendance = Attendance::query()
                ->where('employee_id', $employee->id)
                ->whereDate('attendance_date', $now->toDateString())
                ->first();

            if (! $attendance) {
                throw ValidationException::withMessages([
                    'attendance' => ['Clock in is required before clocking out.'],
                ]);
            }

            $this->addLog($attendance, AttendanceAction::CLOCK_OUT, $image);

            return $attendance->load('logs');
        });
    }

    private function attendanceEmployee(User $user): Employee
    {
        $employee = $user->employee;

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee' => ['The authenticated user is not associated with an employee record.'],
            ]);
        }

        if (! $employee->shift_id) {
            throw ValidationException::withMessages([
                'shift' => ['An assigned shift is required before recording attendance.'],
            ]);
        }

        return $employee;
    }

    private function addLog(Attendance $attendance, AttendanceAction $action, UploadedFile $image): void
    {
        $lastLog = $attendance->logs()->latest('created_at')->first();

        $this->validateAction($lastLog?->action, $action);
        $this->validateMinimumInterval($lastLog?->created_at, now());

        $attendance->logs()->create([
            'action' => $action,
            'image_path' => $image->store('attendance/images', 'local'),
        ]);
    }

    private function validateAction(?AttendanceAction $lastAction, AttendanceAction $action): void
    {
        if ($lastAction === null && $action === AttendanceAction::CLOCK_OUT) {
            throw ValidationException::withMessages([
                'attendance' => ['Clock in is required before clocking out.'],
            ]);
        }

        if ($lastAction === null) {
            return;
        }

        $expectedAction = $lastAction === AttendanceAction::CLOCK_IN
            ? AttendanceAction::CLOCK_OUT
            : AttendanceAction::CLOCK_IN;

        if ($action !== $expectedAction) {
            throw ValidationException::withMessages([
                'attendance' => ["A {$action->value} action must follow a {$expectedAction->value} action."],
            ]);
        }
    }

    private function validateMinimumInterval(?Carbon $lastOccurredAt, Carbon $now): void
    {
        if (! $lastOccurredAt) {
            return;
        }

        $minimumInterval = SystemConfiguration::query()->value('minimum_action_interval_minutes');

        if ($lastOccurredAt->copy()->addMinutes($minimumInterval)->isAfter($now)) {
            throw ValidationException::withMessages([
                'attendance' => ["Attendance actions must be at least {$minimumInterval} minute(s) apart."],
            ]);
        }
    }
}
