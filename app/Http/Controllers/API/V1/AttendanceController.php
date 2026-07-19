<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\Attendance\ClockAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;

class AttendanceController extends ApiController
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function clockIn(ClockAttendanceRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->clockIn($request->user(), $request->file('image'));

        return $this->success(
            new AttendanceResource($attendance),
            'Clocked in successfully.',
            201,
        );
    }

    public function clockOut(ClockAttendanceRequest $request): JsonResponse
    {
        $attendance = $this->attendanceService->clockOut($request->user(), $request->file('image'));

        return $this->success(
            new AttendanceResource($attendance),
            'Clocked out successfully.',
            201,
        );
    }
}
