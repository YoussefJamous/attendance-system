<?php

namespace App\Services;

use App\Enums\AttendanceAction;
use App\Enums\AttendanceCorrectionStatus;
use App\Enums\AttendanceStatus;
use App\Enums\Permission;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Employee;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceCorrectionService
{
    public function index(User $user, int $perPage): LengthAwarePaginator
    {
        $query = AttendanceCorrection::query()
            ->with('logs')
            ->latest();

        if (! $user->can(Permission::ATTENDANCE_CORRECTIONS_MANAGE->value)) {
            $query->whereHas('employee', fn ($employeeQuery) => $employeeQuery->where('user_id', $user->id));
        }

        return $query->paginate($perPage);
    }

    public function store(User $user, array $data): AttendanceCorrection
    {
        $employee = $this->employeeForUser($user);
        $logs = $this->validatedTimeline($data['attendance_date'], $data['logs']);

        if (AttendanceCorrection::query()
            ->where('employee_id', $employee->id)
            ->whereDate('attendance_date', $data['attendance_date'])
            ->where('status', AttendanceCorrectionStatus::PENDING)
            ->exists()) {
            throw ValidationException::withMessages([
                'attendance_date' => ['A pending correction already exists for this date.'],
            ]);
        }

        return DB::transaction(function () use ($employee, $data, $logs) {
            $correction = AttendanceCorrection::create([
                'employee_id' => $employee->id,
                'attendance_date' => $data['attendance_date'],
                'note' => $data['note'],
                'status' => AttendanceCorrectionStatus::PENDING,
            ]);

            $correction->logs()->createMany($logs);

            return $correction->load('logs');
        });
    }

    public function approve(AttendanceCorrection $correction): AttendanceCorrection
    {
        return DB::transaction(function () use ($correction) {
            $correction = AttendanceCorrection::query()
                ->with('logs')
                ->lockForUpdate()
                ->findOrFail($correction->id);

            $this->ensurePending($correction);

            $attendance = Attendance::query()
                ->where('employee_id', $correction->employee_id)
                ->whereDate('attendance_date', $correction->attendance_date)
                ->first();

            if (! $attendance) {
                $attendance = Attendance::create([
                    'employee_id' => $correction->employee_id,
                    'attendance_date' => $correction->attendance_date,
                    'status' => AttendanceStatus::INCOMPLETE,
                ]);
            }

            $attendance->logs()->delete();

            foreach ($correction->logs as $log) {
                $attendanceLog = $attendance->logs()->create([
                    'action' => $log->action,
                    'image_path' => null,
                ]);

                $attendanceLog->forceFill([
                    'created_at' => $log->action_at,
                    'updated_at' => $log->action_at,
                ])->save();
            }

            $correction->update(['status' => AttendanceCorrectionStatus::APPROVED]);

            return $correction->fresh()->load('logs');
        });
    }

    public function reject(AttendanceCorrection $correction): AttendanceCorrection
    {
        return DB::transaction(function () use ($correction) {
            $correction = AttendanceCorrection::query()
                ->lockForUpdate()
                ->findOrFail($correction->id);

            $this->ensurePending($correction);
            $correction->update(['status' => AttendanceCorrectionStatus::REJECTED]);

            return $correction->fresh()->load('logs');
        });
    }

    private function employeeForUser(User $user): Employee
    {
        $employee = $user->employee;

        if (! $employee) {
            throw ValidationException::withMessages([
                'employee' => ['The authenticated user is not associated with an employee record.'],
            ]);
        }

        return $employee;
    }

    private function validatedTimeline(string $attendanceDate, array $logs): array
    {
        $minimumInterval = SystemConfiguration::query()->value('minimum_action_interval_minutes');
        $normalizedLogs = [];
        $previousAction = null;
        $previousActionAt = null;

        foreach ($logs as $index => $log) {
            $action = AttendanceAction::from($log['action']);
            $actionAt = Carbon::parse($log['action_at']);
            $field = "logs.{$index}";

            if ($actionAt->toDateString() !== $attendanceDate) {
                throw ValidationException::withMessages([
                    "{$field}.action_at" => ['Each correction action must be on the selected attendance date.'],
                ]);
            }

            $expectedAction = $previousAction === null
                ? AttendanceAction::CLOCK_IN
                : ($previousAction === AttendanceAction::CLOCK_IN ? AttendanceAction::CLOCK_OUT : AttendanceAction::CLOCK_IN);

            if ($action !== $expectedAction) {
                throw ValidationException::withMessages([
                    "{$field}.action" => ["This action must be {$expectedAction->value}."],
                ]);
            }

            if ($previousActionAt && $actionAt->lt($previousActionAt->copy()->addMinutes($minimumInterval))) {
                throw ValidationException::withMessages([
                    "{$field}.action_at" => ["Attendance actions must be at least {$minimumInterval} minute(s) apart."],
                ]);
            }

            $normalizedLogs[] = [
                'action' => $action,
                'action_at' => $actionAt,
            ];
            $previousAction = $action;
            $previousActionAt = $actionAt;
        }

        if ($previousAction !== AttendanceAction::CLOCK_OUT) {
            throw ValidationException::withMessages([
                'logs' => ['The final correction action must be clock_out.'],
            ]);
        }

        return $normalizedLogs;
    }

    private function ensurePending(AttendanceCorrection $correction): void
    {
        if ($correction->status !== AttendanceCorrectionStatus::PENDING) {
            throw ValidationException::withMessages([
                'attendance_correction' => ['Only pending corrections can be reviewed.'],
            ]);
        }
    }
}
