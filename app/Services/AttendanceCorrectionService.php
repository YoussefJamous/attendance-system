<?php

namespace App\Services;

use App\Enums\AttendanceAction;
use App\Enums\AttendanceCorrectionStatus;
use App\Enums\Permission;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Employee;
use App\Models\SystemConfiguration;
use App\Models\User;
use App\Pipelines\AttendanceCorrection\AttendanceDateFilterPipeline;
use App\Pipelines\AttendanceCorrection\DepartmentFilterPipeline;
use App\Pipelines\AttendanceCorrection\EmployeeFilterPipeline;
use App\Pipelines\AttendanceCorrection\SortPipeline;
use App\Pipelines\AttendanceCorrection\StatusFilterPipeline;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AttendanceCorrectionService
{
    public function index(User $user, array $filters, int $perPage): LengthAwarePaginator
    {
        $query = AttendanceCorrection::query()->with(['attendance', 'logs']);

        if (! $user->can(Permission::ATTENDANCE_CORRECTIONS_MANAGE->value)) {
            $query->whereHas('attendance.employee', fn ($employeeQuery) => $employeeQuery->where('user_id', $user->id));
        }

        $query = app(Pipeline::class)
            ->send($query)
            ->through([
                new StatusFilterPipeline($filters['status'] ?? null),
                new AttendanceDateFilterPipeline(
                    $filters['attendance_date'] ?? null,
                    $filters['date_from'] ?? null,
                    $filters['date_to'] ?? null,
                ),
                new EmployeeFilterPipeline($filters['employee_id'] ?? null),
                new DepartmentFilterPipeline($filters['department_id'] ?? null),
                new SortPipeline(
                    $filters['sort_by'] ?? 'created_at',
                    $filters['sort_direction'] ?? 'desc',
                ),
            ])
            ->thenReturn();

        return $query
            ->paginate($perPage)
            ->appends($filters);
    }

    public function store(User $user, array $data): AttendanceCorrection
    {
        $employee = $this->employeeForUser($user);
        $attendance = Attendance::query()
            ->where('id', $data['attendance_id'])
            ->where('employee_id', $employee->id)
            ->first();

        if (! $attendance) {
            throw ValidationException::withMessages([
                'attendance_id' => ['The selected attendance record is not available.'],
            ]);
        }

        $logs = $this->validatedTimeline($attendance->attendance_date->toDateString(), $data['logs']);

        if (AttendanceCorrection::query()
            ->where('attendance_id', $attendance->id)
            ->where('status', AttendanceCorrectionStatus::PENDING)
            ->exists()) {
            throw ValidationException::withMessages([
                'attendance_id' => ['A pending correction already exists for this attendance record.'],
            ]);
        }

        return DB::transaction(function () use ($attendance, $data, $logs) {
            $correction = AttendanceCorrection::create([
                'attendance_id' => $attendance->id,
                'note' => $data['note'],
                'status' => AttendanceCorrectionStatus::PENDING,
            ]);

            $correction->logs()->createMany($logs);

            return $correction->load(['attendance', 'logs']);
        });
    }

    public function approve(AttendanceCorrection $correction): AttendanceCorrection
    {
        return DB::transaction(function () use ($correction) {
            $correction = AttendanceCorrection::query()
                ->with(['attendance', 'logs'])
                ->lockForUpdate()
                ->findOrFail($correction->id);

            $this->ensurePending($correction);

            $attendance = $correction->attendance;

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

            return $correction->fresh()->load(['attendance', 'logs']);
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

            return $correction->fresh()->load(['attendance', 'logs']);
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
