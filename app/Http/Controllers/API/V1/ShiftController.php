<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\Shift\IndexShiftRequest;
use App\Http\Requests\Shift\StoreShiftRequest;
use App\Http\Requests\Shift\UpdateShiftRequest;
use App\Http\Resources\ShiftResource;
use App\Models\Shift;
use App\Services\ShiftService;
use Illuminate\Http\JsonResponse;

class ShiftController extends ApiController
{
    public function __construct(private readonly ShiftService $shiftService) {}

    public function index(IndexShiftRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Shift::class);

        $shifts = $this->shiftService->index(
            $request->validated(),
            $request->integer('per_page', $this->per_page),
        );

        return $this->success(
            [
                'shifts' => ShiftResource::collection($shifts),
                'pagination' => $this->paginationData($shifts),
            ],
            'Shifts retrieved successfully.'
        );
    }

    public function store(StoreShiftRequest $request): JsonResponse
    {
        $this->authorize('create', Shift::class);

        $shift = $this->shiftService->store($request->validated());

        return $this->success(new ShiftResource($shift), 'Shift created successfully.', 201);
    }

    public function show(Shift $shift): JsonResponse
    {
        $this->authorize('view', $shift);

        return $this->success(
            new ShiftResource($this->shiftService->show($shift)),
            'Shift retrieved successfully.'
        );
    }

    public function update(UpdateShiftRequest $request, Shift $shift): JsonResponse
    {
        $this->authorize('update', $shift);

        return $this->success(
            new ShiftResource($this->shiftService->update($shift, $request->validated())),
            'Shift updated successfully.'
        );
    }

    public function destroy(Shift $shift): JsonResponse
    {
        $this->authorize('delete', $shift);

        $this->shiftService->delete($shift);

        return $this->success(null, 'Shift deleted successfully.');
    }
}
