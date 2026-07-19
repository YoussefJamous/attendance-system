<?php

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\Holiday;
use App\Models\User;
use App\Services\HolidayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Spatie\Permission\Models\Permission as SpatiePermission;
use Tests\TestCase;

class HolidayManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_authorized_user_can_create_overlapping_holidays(): void
    {
        $user = $this->userWithPermission(Permission::HOLIDAYS_CREATE);

        $this->actingAs($user)->postJson('/api/v1/holidays', [
            'name' => 'Year End Closure',
            'description' => 'Office closure.',
            'start_date' => '2026-12-24',
            'end_date' => '2026-12-31',
        ])->assertCreated();

        $response = $this->actingAs($user)->postJson('/api/v1/holidays', [
            'name' => 'Christmas Day',
            'start_date' => '2026-12-25',
            'end_date' => '2026-12-25',
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.name', 'Christmas Day');

        $this->assertDatabaseCount('holidays', 2);
    }

    public function test_holiday_end_date_cannot_be_before_start_date(): void
    {
        $user = $this->userWithPermission(Permission::HOLIDAYS_CREATE);

        $response = $this->actingAs($user)->postJson('/api/v1/holidays', [
            'name' => 'Invalid Holiday',
            'start_date' => '2026-08-31',
            'end_date' => '2026-08-30',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_authorized_user_can_update_and_delete_a_holiday(): void
    {
        $holiday = Holiday::create([
            'name' => 'Old Holiday',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-01',
        ]);

        $updateUser = $this->userWithPermission(Permission::HOLIDAYS_UPDATE);
        $this->actingAs($updateUser)->putJson("/api/v1/holidays/{$holiday->id}", [
            'name' => 'Updated Holiday',
        ])->assertOk()->assertJsonPath('data.name', 'Updated Holiday');

        $deleteUser = $this->userWithPermission(Permission::HOLIDAYS_DELETE);
        $this->actingAs($deleteUser)->deleteJson("/api/v1/holidays/{$holiday->id}")->assertOk();

        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }

    public function test_partial_holiday_date_update_must_preserve_a_valid_range(): void
    {
        $holiday = Holiday::create([
            'name' => 'Company Break',
            'start_date' => '2026-12-24',
            'end_date' => '2026-12-31',
        ]);
        $user = $this->userWithPermission(Permission::HOLIDAYS_UPDATE);

        $response = $this->actingAs($user)->putJson("/api/v1/holidays/{$holiday->id}", [
            'start_date' => '2027-01-01',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['end_date']);
    }

    public function test_authorized_user_can_import_holidays_from_an_excel_file(): void
    {
        $user = $this->userWithPermission(Permission::HOLIDAYS_CREATE);

        $response = $this->actingAs($user)->post('/api/v1/holidays/import', [
            'file' => $this->holidayImportFile([
                ['National Day', 'Public holiday.', '2026-08-31', '2026-08-31'],
                ['Company Break', null, '2026-12-24', '2026-12-31'],
            ]),
        ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.imported', 2);

        $this->assertDatabaseHas('holidays', ['name' => 'National Day']);
        $this->assertDatabaseHas('holidays', ['name' => 'Company Break']);
    }

    public function test_holiday_import_rejects_an_unreadable_excel_file(): void
    {
        $user = $this->userWithPermission(Permission::HOLIDAYS_CREATE);

        $response = $this->actingAs($user)->post('/api/v1/holidays/import', [
            'file' => UploadedFile::fake()->createWithContent('holidays.xlsx', 'not an Excel workbook'),
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['file']);
    }

    public function test_holiday_helper_detects_dates_within_a_holiday_range(): void
    {
        Holiday::create([
            'name' => 'Company Break',
            'start_date' => '2026-12-24',
            'end_date' => '2026-12-31',
        ]);

        $service = app(HolidayService::class);

        $this->assertTrue($service->isHoliday('2026-12-27'));
        $this->assertFalse($service->isHoliday('2027-01-01'));
    }

    public function test_authorized_user_can_download_the_import_template(): void
    {
        $user = $this->userWithPermission(Permission::HOLIDAYS_VIEW);

        $this->actingAs($user)
            ->get('/api/v1/holidays/import-template')
            ->assertOk()
            ->assertDownload('holiday-import-template.xlsx');
    }

    public function test_user_without_holiday_permission_cannot_view_holidays(): void
    {
        $response = $this->actingAs(User::factory()->create())->getJson('/api/v1/holidays');

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

    private function holidayImportFile(array $rows): UploadedFile
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->fromArray(['name', 'description', 'start_date', 'end_date'], null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $path = tempnam(sys_get_temp_dir(), 'holidays');
        (new Xlsx($spreadsheet))->save($path);

        return new UploadedFile(
            $path,
            'holidays.xlsx',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            null,
            true,
        );
    }
}
