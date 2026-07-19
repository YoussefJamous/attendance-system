<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Enums\Role;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Spatie\Permission\Models\Role as SpatieRole;
use Tests\TestCase;

class ShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_creates_a_shift_and_assigns_it_to_employees(): void
    {
        $this->seed(DatabaseSeeder::class);

        $shift = Shift::where('name', 'Morning Shift')->firstOrFail();

        $this->assertCount(5, $shift->days);
        $this->assertDatabaseCount('employees', 20);
        $this->assertDatabaseMissing('employees', ['shift_id' => null]);
    }

    public function test_authorized_user_can_create_a_shift_with_days(): void
    {
        $user = $this->userWithPermission(Permission::SHIFTS_CREATE);

        $response = $this->actingAs($user)->postJson('/api/v1/shifts', $this->shiftData());

        $response
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Morning Shift')
            ->assertJsonPath('data.days.0.day_of_week', 'friday')
            ->assertJsonPath('data.days.1.break_duration_minutes', 60);

        $this->assertDatabaseHas('shifts', ['name' => 'Morning Shift']);
        $this->assertDatabaseCount('shift_days', 2);
    }

    public function test_authorized_user_can_create_an_employee_without_a_shift(): void
    {
        $user = $this->userWithPermission(Permission::EMPLOYEES_CREATE);
        SpatieRole::findOrCreate(Role::EMPLOYEE->value, 'web');

        $response = $this->actingAs($user)->postJson('/api/v1/employees', [
            'first_name' => 'Amina',
            'last_name' => 'Rahman',
            'email' => 'amina.rahman@example.com',
            'gender' => 'female',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('employees', [
            'first_name' => 'Amina',
            'last_name' => 'Rahman',
            'shift_id' => null,
        ]);
    }

    public function test_shift_days_require_unique_weekdays(): void
    {
        $user = $this->userWithPermission(Permission::SHIFTS_CREATE);
        $data = $this->shiftData();
        $data['days'][1]['day_of_week'] = 'monday';

        $response = $this->actingAs($user)->postJson('/api/v1/shifts', $data);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['days.1.day_of_week']);
    }

    public function test_shift_allows_an_overnight_schedule_without_extra_configuration(): void
    {
        $user = $this->userWithPermission(Permission::SHIFTS_CREATE);
        $data = $this->shiftData();
        $data['days'] = [[
            'day_of_week' => 'friday',
            'work_start_time' => '22:00',
            'work_end_time' => '06:00',
            'break_duration_minutes' => 60,
        ]];

        $response = $this->actingAs($user)->postJson('/api/v1/shifts', $data);

        $response
            ->assertCreated()
            ->assertJsonPath('data.days.0.work_end_time', '06:00');
    }

    public function test_authorized_user_can_update_the_complete_shift_aggregate(): void
    {
        $user = $this->userWithPermission(Permission::SHIFTS_UPDATE);
        $shift = Shift::create(['name' => 'Old Shift']);
        $shift->days()->create([
            'day_of_week' => 'sunday',
            'work_start_time' => '09:00',
            'work_end_time' => '18:00',
            'break_duration_minutes' => 30,
        ]);

        $data = $this->shiftData();
        $data['name'] = 'Updated Shift';

        $response = $this->actingAs($user)->putJson("/api/v1/shifts/{$shift->id}", $data);

        $response
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Shift')
            ->assertJsonCount(2, 'data.days');

        $this->assertDatabaseMissing('shift_days', ['day_of_week' => 'sunday', 'break_duration_minutes' => 30]);
        $this->assertDatabaseCount('shift_days', 2);
    }

    public function test_shift_cannot_be_deleted_while_assigned_to_an_employee(): void
    {
        $user = $this->userWithPermission(Permission::SHIFTS_DELETE);
        $shift = Shift::create(['name' => 'Assigned Shift']);
        Employee::factory()->for(User::factory())->create(['shift_id' => $shift->id]);

        $response = $this->actingAs($user)->deleteJson("/api/v1/shifts/{$shift->id}");

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['shift']);

        $this->assertDatabaseHas('shifts', ['id' => $shift->id]);
    }

    public function test_user_without_shift_permission_cannot_view_shifts(): void
    {
        $response = $this->actingAs(User::factory()->create())->getJson('/api/v1/shifts');

        $response
            ->assertForbidden()
            ->assertJsonPath('success', false);
    }

    private function userWithPermission(Permission $permission): User
    {
        $user = User::factory()->create();
        SpatiePermission::findOrCreate($permission->value, 'web');
        $user->givePermissionTo($permission->value);

        return $user;
    }

    private function shiftData(): array
    {
        return [
            'name' => 'Morning Shift',
            'description' => 'Weekday office schedule.',
            'is_active' => true,
            'days' => [
                [
                    'day_of_week' => 'monday',
                    'work_start_time' => '09:00',
                    'work_end_time' => '18:00',
                    'break_duration_minutes' => 60,
                ],
                [
                    'day_of_week' => 'friday',
                    'work_start_time' => '09:00',
                    'work_end_time' => '18:00',
                    'break_duration_minutes' => 90,
                ],
            ],
        ];
    }
}
