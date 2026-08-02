<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\AttendanceCorrection\StoreAttendanceCorrectionRequest;
use App\Http\Resources\AttendanceCorrectionResource;
use App\Models\AttendanceCorrection;
use App\Services\AttendanceCorrectionService;
use Illuminate\Http\JsonResponse;

class AttendanceCorrectionController extends ApiController
{
    public function __construct(private readonly AttendanceCorrectionService $attendanceCorrectionService) {}

    public function index(): JsonResponse
    {
        $this->authorize('viewAny', AttendanceCorrection::class);

        $corrections = $this->attendanceCorrectionService->index(
            request()->user(),
            request()->integer('per_page', $this->per_page),
        );

        return $this->success([
            'attendance_corrections' => AttendanceCorrectionResource::collection($corrections),
            'pagination' => $this->paginationData($corrections),
        ], 'Attendance corrections retrieved successfully.');
    }

    public function store(StoreAttendanceCorrectionRequest $request): JsonResponse
    {
        $this->authorize('create', AttendanceCorrection::class);

        return $this->success(
            new AttendanceCorrectionResource(
                $this->attendanceCorrectionService->store($request->user(), $request->validated()),
            ),
            'Attendance correction submitted successfully.',
            201,
        );
    }

    public function show(AttendanceCorrection $attendanceCorrection): JsonResponse
    {
        $this->authorize('view', $attendanceCorrection);

        return $this->success(
            new AttendanceCorrectionResource($attendanceCorrection->load(['attendance', 'logs'])),
            'Attendance correction retrieved successfully.',
        );
    }

    public function approve(AttendanceCorrection $attendanceCorrection): JsonResponse
    {
        $this->authorize('approve', $attendanceCorrection);

        return $this->success(
            new AttendanceCorrectionResource($this->attendanceCorrectionService->approve($attendanceCorrection)),
            'Attendance correction approved successfully.',
        );
    }

    public function reject(AttendanceCorrection $attendanceCorrection): JsonResponse
    {
        $this->authorize('reject', $attendanceCorrection);

        return $this->success(
            new AttendanceCorrectionResource($this->attendanceCorrectionService->reject($attendanceCorrection)),
            'Attendance correction rejected successfully.',
        );
    }
}
