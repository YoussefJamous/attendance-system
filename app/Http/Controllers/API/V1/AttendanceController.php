<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\Attendance\ClockAttendanceRequest;
use App\Http\Requests\Attendance\IndexAttendanceRequest;
use App\Http\Resources\AttendanceResource;
use App\Models\Attendance;
use App\Services\AttendanceService;
use Illuminate\Http\JsonResponse;

class AttendanceController extends ApiController
{
    public function __construct(private readonly AttendanceService $attendanceService) {}

    public function index(IndexAttendanceRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Attendance::class);

        $attendances = $this->attendanceService->index(
            $request->user(),
            $request->validated(),
            $request->integer('per_page', $this->per_page),
        );

        return $this->success([
            'attendances' => AttendanceResource::collection($attendances),
            'pagination' => $this->paginationData($attendances),
        ], 'Attendances retrieved successfully.');
    }

    public function show(Attendance $attendance): JsonResponse
    {
        $this->authorize('view', $attendance);

        return $this->success(
            new AttendanceResource($this->attendanceService->show($attendance)),
            'Attendance retrieved successfully.',
        );
    }

    public function record(ClockAttendanceRequest $request): JsonResponse
    {
        $this->authorize('record', Attendance::class);

        $attendance = $this->attendanceService->record($request->user(), $request->file('image'));

        return $this->success(
            new AttendanceResource($attendance),
            'Attendance action recorded successfully.',
            201,
        );
    }
}
