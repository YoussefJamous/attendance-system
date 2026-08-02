<?php

namespace Tests\Feature;

use App\Enums\AttendanceAction;
use App\Enums\AttendanceCorrectionStatus;
use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Enums\Permission;
use App\Models\Attendance;
use App\Models\AttendanceCorrection;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Tests\TestCase;

class AttendanceCorrectionTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_submit_a_full_attendance_correction_without_changing_existing_logs(): void
    {
        $user = $this->employeeWithPermissions(
            Permission::ATTENDANCE_CORRECTIONS_CREATE,
            Permission::ATTENDANCE_CORRECTIONS_VIEW,
        );
        $attendance = $this->attendanceWithLog($user, '2026-07-20', '2026-07-20 09:00:00');
        $this->createConfiguration();

        $response = $this->actingAs($user)->postJson('/api/v1/attendance-corrections', [
            'attendance_id' => $attendance->id,
            'note' => 'Forgot to clock out after lunch.',
            'logs' => [
                ['action' => 'clock_in', 'action_at' => '2026-07-20 09:00:00'],
                ['action' => 'clock_out', 'action_at' => '2026-07-20 13:00:00'],
                ['action' => 'clock_in', 'action_at' => '2026-07-20 14:00:00'],
                ['action' => 'clock_out', 'action_at' => '2026-07-20 18:00:00'],
            ],
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.attendance_id', $attendance->id)
            ->assertJsonPath('data.status', AttendanceCorrectionStatus::PENDING->value)
            ->assertJsonCount(4, 'data.logs');

        $this->assertDatabaseCount('attendance_logs', 1);
        $this->assertSame('clock_in', $attendance->logs()->firstOrFail()->action->value);
    }

    public function test_hr_approval_replaces_the_attendance_logs_with_the_proposed_timeline(): void
    {
        $employee = $this->employeeWithPermissions(Permission::ATTENDANCE_CORRECTIONS_CREATE);
        $attendance = $this->attendanceWithLog($employee, '2026-07-20', '2026-07-20 09:00:00');
        $this->createConfiguration();
        $correction = $this->submitCorrection($employee);
        $hr = $this->userWithPermission(Permission::ATTENDANCE_CORRECTIONS_MANAGE);

        $response = $this->actingAs($hr)->patchJson("/api/v1/attendance-corrections/{$correction->id}/approve");

        $response
            ->assertOk()
            ->assertJsonPath('data.status', AttendanceCorrectionStatus::APPROVED->value);

        $this->assertDatabaseCount('attendance_logs', 4);
        $this->assertDatabaseHas('attendance_corrections', [
            'id' => $correction->id,
            'status' => AttendanceCorrectionStatus::APPROVED->value,
        ]);

        $attendance->refresh();
        $logs = $attendance->logs()->orderBy('created_at')->get();

        $this->assertSame('clock_in', $logs->first()->action->value);
        $this->assertSame('2026-07-20 09:00:00', $logs->first()->created_at->format('Y-m-d H:i:s'));
        $this->assertNull($logs->first()->image_path);
        $this->assertSame('clock_out', $logs->last()->action->value);
    }

    public function test_hr_rejection_keeps_existing_attendance_logs_unchanged(): void
    {
        $employee = $this->employeeWithPermissions(Permission::ATTENDANCE_CORRECTIONS_CREATE);
        $attendance = $this->attendanceWithLog($employee, '2026-07-20', '2026-07-20 09:00:00');
        $this->createConfiguration();
        $correction = $this->submitCorrection($employee);
        $hr = $this->userWithPermission(Permission::ATTENDANCE_CORRECTIONS_MANAGE);

        $this->actingAs($hr)
            ->patchJson("/api/v1/attendance-corrections/{$correction->id}/reject")
            ->assertOk()
            ->assertJsonPath('data.status', AttendanceCorrectionStatus::REJECTED->value);

        $this->assertDatabaseCount('attendance_logs', 1);
        $this->assertSame('clock_in', $attendance->fresh()->logs()->firstOrFail()->action->value);
    }

    public function test_correction_timeline_must_alternate_and_end_with_clock_out(): void
    {
        $user = $this->employeeWithPermissions(Permission::ATTENDANCE_CORRECTIONS_CREATE);
        $attendance = $this->attendanceFor($user, '2026-07-20');
        $this->createConfiguration();

        $response = $this->actingAs($user)->postJson('/api/v1/attendance-corrections', [
            'attendance_id' => $attendance->id,
            'note' => 'Incorrect sequence.',
            'logs' => [
                ['action' => 'clock_in', 'action_at' => '2026-07-20 09:00:00'],
                ['action' => 'clock_in', 'action_at' => '2026-07-20 18:00:00'],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['logs.1.action']);
    }

    public function test_correction_timeline_must_respect_the_configured_minimum_interval(): void
    {
        $user = $this->employeeWithPermissions(Permission::ATTENDANCE_CORRECTIONS_CREATE);
        $attendance = $this->attendanceFor($user, '2026-07-20');
        $this->createConfiguration(['minimum_action_interval_minutes' => 2]);

        $response = $this->actingAs($user)->postJson('/api/v1/attendance-corrections', [
            'attendance_id' => $attendance->id,
            'note' => 'Incorrect interval.',
            'logs' => [
                ['action' => 'clock_in', 'action_at' => '2026-07-20 09:00:00'],
                ['action' => 'clock_out', 'action_at' => '2026-07-20 09:01:00'],
            ],
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['logs.1.action_at']);
    }

    public function test_employee_can_only_view_their_own_attendance_corrections(): void
    {
        $firstEmployee = $this->employeeWithPermissions(
            Permission::ATTENDANCE_CORRECTIONS_CREATE,
            Permission::ATTENDANCE_CORRECTIONS_VIEW,
        );
        $secondEmployee = $this->employeeWithPermissions(
            Permission::ATTENDANCE_CORRECTIONS_CREATE,
            Permission::ATTENDANCE_CORRECTIONS_VIEW,
        );
        $this->createConfiguration();
        $this->attendanceFor($firstEmployee, '2026-07-20');
        $correction = $this->submitCorrection($firstEmployee);

        $this->actingAs($secondEmployee)
            ->getJson("/api/v1/attendance-corrections/{$correction->id}")
            ->assertForbidden();
    }

    public function test_hr_can_filter_and_sort_attendance_corrections(): void
    {
        $this->createConfiguration();
        $engineering = Department::create(['name' => 'Engineering', 'code' => 'ENG']);
        $design = Department::create(['name' => 'Design', 'code' => 'DSN']);
        $firstEmployee = $this->employeeWithPermissions();
        $secondEmployee = $this->employeeWithPermissions();
        $firstEmployee->employee->update(['department_id' => $engineering->id]);
        $secondEmployee->employee->update(['department_id' => $design->id]);

        $firstCorrection = $this->correctionFor($firstEmployee, '2026-07-20', AttendanceCorrectionStatus::PENDING);
        $this->correctionFor($firstEmployee, '2026-07-22', AttendanceCorrectionStatus::PENDING);
        $this->correctionFor($secondEmployee, '2026-07-21', AttendanceCorrectionStatus::APPROVED);
        $hr = $this->userWithPermission(Permission::ATTENDANCE_CORRECTIONS_MANAGE);

        $response = $this->actingAs($hr)->getJson('/api/v1/attendance-corrections?status=pending&date_from=2026-07-20&date_to=2026-07-21&employee_id='.$firstEmployee->employee->id.'&department_id='.$engineering->id.'&sort_by=attendance_date&sort_direction=asc');

        $response
            ->assertOk()
            ->assertJsonCount(1, 'data.attendance_corrections')
            ->assertJsonPath('data.attendance_corrections.0.id', $firstCorrection->id);
    }

    public function test_employee_index_is_scoped_to_their_corrections_and_rejects_hr_only_filters(): void
    {
        $this->createConfiguration();
        $firstEmployee = $this->employeeWithPermissions(Permission::ATTENDANCE_CORRECTIONS_VIEW);
        $secondEmployee = $this->employeeWithPermissions(Permission::ATTENDANCE_CORRECTIONS_VIEW);
        $firstCorrection = $this->correctionFor($firstEmployee, '2026-07-20', AttendanceCorrectionStatus::PENDING);
        $this->correctionFor($secondEmployee, '2026-07-20', AttendanceCorrectionStatus::PENDING);

        $this->actingAs($firstEmployee)
            ->getJson('/api/v1/attendance-corrections')
            ->assertOk()
            ->assertJsonCount(1, 'data.attendance_corrections')
            ->assertJsonPath('data.attendance_corrections.0.id', $firstCorrection->id);

        $this->actingAs($firstEmployee)
            ->getJson('/api/v1/attendance-corrections?employee_id='.$secondEmployee->employee->id)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_id']);
    }

    private function submitCorrection(User $user): AttendanceCorrection
    {
        $attendance = Attendance::query()
            ->where('employee_id', $user->employee->id)
            ->whereDate('attendance_date', '2026-07-20')
            ->firstOrFail();

        $this->actingAs($user)->postJson('/api/v1/attendance-corrections', [
            'attendance_id' => $attendance->id,
            'note' => 'Correcting my full attendance timeline.',
            'logs' => [
                ['action' => 'clock_in', 'action_at' => '2026-07-20 09:00:00'],
                ['action' => 'clock_out', 'action_at' => '2026-07-20 13:00:00'],
                ['action' => 'clock_in', 'action_at' => '2026-07-20 14:00:00'],
                ['action' => 'clock_out', 'action_at' => '2026-07-20 18:00:00'],
            ],
        ])->assertCreated();

        return AttendanceCorrection::query()->firstOrFail();
    }

    private function attendanceWithLog(User $user, string $date, string $actionAt): Attendance
    {
        $attendance = $this->attendanceFor($user, $date);
        $attendance->logs()->create([
            'action' => AttendanceAction::CLOCK_IN,
            'image_path' => 'attendance/images/original.jpg',
            'created_at' => Carbon::parse($actionAt),
            'updated_at' => Carbon::parse($actionAt),
        ]);

        return $attendance;
    }

    private function attendanceFor(User $user, string $date): Attendance
    {
        return Attendance::create([
            'employee_id' => $user->employee->id,
            'attendance_date' => $date,
            'status' => AttendanceStatus::INCOMPLETE,
        ]);
    }

    private function correctionFor(User $user, string $date, AttendanceCorrectionStatus $status): AttendanceCorrection
    {
        return AttendanceCorrection::create([
            'attendance_id' => $this->attendanceFor($user, $date)->id,
            'note' => 'Attendance correction for filtering.',
            'status' => $status,
        ]);
    }

    private function createConfiguration(array $attributes = []): SystemConfiguration
    {
        return SystemConfiguration::create([
            'attendance_method' => AttendanceMethod::GPS,
            'minimum_action_interval_minutes' => 1,
            'grace_minutes' => 0,
            ...$attributes,
        ]);
    }

    private function employeeWithPermissions(Permission ...$permissions): User
    {
        $user = User::factory()->create();
        $shift = Shift::create(['name' => fake()->unique()->words(2, true)]);
        Employee::factory()->for($user)->create(['shift_id' => $shift->id]);

        foreach ($permissions as $permission) {
            $this->grantPermission($user, $permission);
        }

        return $user;
    }

    private function userWithPermission(Permission $permission): User
    {
        $user = User::factory()->create();
        $this->grantPermission($user, $permission);

        return $user;
    }

    private function grantPermission(User $user, Permission $permission): void
    {
        SpatiePermission::findOrCreate($permission->value, 'web');
        $user->givePermissionTo($permission->value);
    }
}
