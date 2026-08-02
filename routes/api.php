<?php

use App\Http\Controllers\API\V1;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->controller(V1\AuthController::class)->group(function () {
        Route::post('/login', 'login');

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', 'logout');
            Route::get('/me', 'me');
        });
    });

    Route::middleware(['auth:sanctum'])->group(function () {

        // Employee routes
        Route::apiResource('employees', V1\EmployeeController::class);
        Route::prefix('employees')->controller(V1\EmployeeController::class)->group(function () {
            Route::patch('restore/{employee}', 'restore')->withTrashed();
            // Route::delete('force/{employee}', 'forceDelete')->withTrashed();
        });

        // Department Routes
        Route::apiResource('departments', V1\DepartmentController::class);

        // Shift Routes
        Route::apiResource('shifts', V1\ShiftController::class);

        // Holiday Routes
        Route::prefix('holidays')->controller(V1\HolidayController::class)->group(function () {
            Route::get('import-template', 'downloadTemplate');
            Route::post('import', 'import');
        });
        Route::apiResource('holidays', V1\HolidayController::class);

        // System Configuration Routes
        Route::get('system-configuration', [V1\SystemConfigurationController::class, 'show']);
        Route::put('system-configuration', [V1\SystemConfigurationController::class, 'update']);

        // Attendance Routes
        Route::middleware('attendance.configured')->prefix('attendance')->controller(V1\AttendanceController::class)->group(function () {
            Route::post('actions', 'record');
        });

        // Attendance Correction Routes
        Route::middleware('attendance.configured')->group(function () {
            Route::apiResource('attendance-corrections', V1\AttendanceCorrectionController::class)
                ->only(['index', 'store', 'show'])
                ->parameters(['attendance-corrections' => 'attendanceCorrection']);

            Route::prefix('attendance-corrections')->controller(V1\AttendanceCorrectionController::class)->group(function () {
                Route::patch('{attendanceCorrection}/approve', 'approve');
                Route::patch('{attendanceCorrection}/reject', 'reject');
            });
        });
    });
});
