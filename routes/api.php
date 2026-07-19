<?php

use App\Http\Controllers\API\V1;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

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
    });
});
