<?php

namespace Tests\Feature;

use App\Enums\AttendanceMethod;
use App\Enums\AttendanceStatus;
use App\Enums\Permission;
use App\Models\Attendance;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\SystemConfiguration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Tests\TestCase;

class AttendanceTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_hr_can_save_the_global_system_configuration(): void
    {
        $user = $this->userWithPermission(Permission::SYSTEM_CONFIGURATION_MANAGE);

        $response = $this->actingAs($user)->putJson('/api/v1/system-configuration', [
            'attendance_method' => AttendanceMethod::GPS->value,
            'minimum_action_interval_minutes' => 2,
            'grace_minutes' => 10,
        ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.attendance_method', 'gps')
            ->assertJsonPath('data.minimum_action_interval_minutes', 2)
            ->assertJsonPath('data.grace_minutes', 10);

        $this->assertDatabaseCount('system_configurations', 1);
    }

    public function test_attendance_actions_are_blocked_until_configuration_exists(): void
    {
        $response = $this->actingAs(User::factory()->create())->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-in.jpg'),
        ]);

        $response
            ->assertConflict()
            ->assertJsonPath('message', 'Attendance configuration has not been completed.');
    }

    public function test_employee_can_record_alternating_attendance_actions_with_required_images(): void
    {
        Storage::fake('local');
        $user = $this->attendanceUser();
        SystemConfiguration::create([
            'attendance_method' => AttendanceMethod::GPS,
            'minimum_action_interval_minutes' => 1,
            'grace_minutes' => 0,
        ]);

        Carbon::setTestNow('2026-07-20 09:00:00');
        $clockIn = $this->actingAs($user)->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-in.jpg'),
        ]);

        $clockIn
            ->assertCreated()
            ->assertJsonPath('data.logs.0.action', 'clock_in')
            ->assertJsonPath('data.logs.0.created_at', '2026-07-20T09:00:00.000000Z');

        Carbon::setTestNow('2026-07-20 09:01:00');
        $clockOut = $this->actingAs($user)->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-out.png'),
        ]);

        $clockOut
            ->assertCreated()
            ->assertJsonCount(2, 'data.logs')
            ->assertJsonPath('data.logs.1.action', 'clock_out');

        Storage::disk('local')->assertExists($clockIn->json('data.logs.0.image_path'));
        Storage::disk('local')->assertExists($clockOut->json('data.logs.1.image_path'));
    }

    public function test_first_attendance_action_is_clock_in(): void
    {
        $user = $this->attendanceUser();
        $this->createConfiguration();

        $response = $this->actingAs($user)->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-in.jpg'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.logs.0.action', 'clock_in');
    }

    public function test_attendance_actions_must_follow_the_configured_interval(): void
    {
        $user = $this->attendanceUser();
        $this->createConfiguration(['minimum_action_interval_minutes' => 2]);

        Carbon::setTestNow('2026-07-20 09:00:00');
        $this->actingAs($user)->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-in.jpg'),
        ])->assertCreated();

        Carbon::setTestNow('2026-07-20 09:01:00');
        $response = $this->actingAs($user)->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-out.jpg'),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['attendance']);
    }

    public function test_attendance_action_is_derived_from_the_latest_log(): void
    {
        $user = $this->attendanceUser();
        $this->createConfiguration();

        Carbon::setTestNow('2026-07-20 09:00:00');
        $this->actingAs($user)->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-in.jpg'),
        ])->assertCreated();

        Carbon::setTestNow('2026-07-20 09:01:00');
        $response = $this->actingAs($user)->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-out.jpg'),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.logs.1.action', 'clock_out');
    }

    public function test_employee_requires_attendance_record_permission(): void
    {
        $user = $this->attendanceUser(false);
        $this->createConfiguration();

        $response = $this->actingAs($user)->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-in.jpg'),
        ]);

        $response->assertForbidden();
    }

    public function test_employee_requires_an_assigned_shift_to_record_attendance(): void
    {
        $user = User::factory()->create();
        Employee::factory()->for($user)->create();
        $this->grantPermission($user, Permission::ATTENDANCE_RECORD);
        $this->createConfiguration();

        $response = $this->actingAs($user)->post('/api/v1/attendance/actions', [
            'image' => UploadedFile::fake()->image('clock-in.jpg'),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['shift']);
    }

    public function test_clock_actions_require_an_image(): void
    {
        $user = $this->attendanceUser();
        $this->createConfiguration();

        $response = $this->actingAs($user)->postJson('/api/v1/attendance/actions');

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['image']);
    }

    public function test_hr_can_filter_and_sort_attendances(): void
    {
        $this->createConfiguration();
        $firstEmployee = $this->attendanceUser();
        $secondEmployee = $this->attendanceUser();
        $firstEmployee->employee->update(['first_name' => 'Amina', 'last_name' => 'Rahman']);
        $secondEmployee->employee->update(['first_name' => 'Bilal', 'last_name' => 'Aziz']);
        $firstAttendance = $this->attendanceFor($firstEmployee, '2026-07-20');
        $this->attendanceFor($secondEmployee, '2026-07-21', AttendanceStatus::COMPLETED);
        $hr = $this->userWithPermission(Permission::ATTENDANCE_MANAGE);

        $this->actingAs($hr)
            ->getJson('/api/v1/attendance?employee_name=Amina&date_from=2026-07-20&date_to=2026-07-20&status=incomplete&sort_by=employee_name&sort_direction=asc')
            ->assertOk()
            ->assertJsonCount(1, 'data.attendances')
            ->assertJsonPath('data.attendances.0.id', $firstAttendance->id)
            ->assertJsonPath('data.attendances.0.employee_name', 'Amina Rahman');
    }

    public function test_employee_can_view_only_their_own_attendances(): void
    {
        $this->createConfiguration();
        $firstEmployee = $this->attendanceUser();
        $secondEmployee = $this->attendanceUser();
        $firstAttendance = $this->attendanceFor($firstEmployee, '2026-07-20');
        $secondAttendance = $this->attendanceFor($secondEmployee, '2026-07-20');

        $this->actingAs($firstEmployee)
            ->getJson('/api/v1/attendance')
            ->assertOk()
            ->assertJsonCount(1, 'data.attendances')
            ->assertJsonPath('data.attendances.0.id', $firstAttendance->id);

        $this->actingAs($firstEmployee)
            ->getJson('/api/v1/attendance?employee_name=Other')
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['employee_name']);

        $this->actingAs($firstEmployee)
            ->getJson("/api/v1/attendance/{$firstAttendance->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $firstAttendance->id);

        $this->actingAs($firstEmployee)
            ->getJson("/api/v1/attendance/{$secondAttendance->id}")
            ->assertForbidden();
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

    private function attendanceUser(bool $withAttendancePermissions = true): User
    {
        $user = User::factory()->create();
        $shift = Shift::create(['name' => fake()->unique()->words(2, true)]);
        Employee::factory()->for($user)->create(['shift_id' => $shift->id]);

        if ($withAttendancePermissions) {
            $this->grantPermission($user, Permission::ATTENDANCE_RECORD);
            $this->grantPermission($user, Permission::ATTENDANCE_VIEW);
        }

        return $user;
    }

    private function attendanceFor(User $user, string $date, AttendanceStatus $status = AttendanceStatus::INCOMPLETE): Attendance
    {
        return Attendance::create([
            'employee_id' => $user->employee->id,
            'attendance_date' => $date,
            'status' => $status,
        ]);
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
