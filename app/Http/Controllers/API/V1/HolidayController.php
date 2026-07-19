<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\Holiday\ImportHolidayRequest;
use App\Http\Requests\Holiday\IndexHolidayRequest;
use App\Http\Requests\Holiday\StoreHolidayRequest;
use App\Http\Requests\Holiday\UpdateHolidayRequest;
use App\Http\Resources\HolidayResource;
use App\Models\Holiday;
use App\Services\HolidayService;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class HolidayController extends ApiController
{
    public function __construct(private readonly HolidayService $holidayService) {}

    public function index(IndexHolidayRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Holiday::class);

        $holidays = $this->holidayService->index(
            $request->validated(),
            $request->integer('per_page', $this->per_page),
        );

        return $this->success(
            [
                'holidays' => HolidayResource::collection($holidays),
                'pagination' => $this->paginationData($holidays),
            ],
            'Holidays retrieved successfully.'
        );
    }

    public function store(StoreHolidayRequest $request): JsonResponse
    {
        $this->authorize('create', Holiday::class);

        return $this->success(
            new HolidayResource($this->holidayService->store($request->validated())),
            'Holiday created successfully.',
            201,
        );
    }

    public function show(Holiday $holiday): JsonResponse
    {
        $this->authorize('view', $holiday);

        return $this->success(new HolidayResource($holiday), 'Holiday retrieved successfully.');
    }

    public function update(UpdateHolidayRequest $request, Holiday $holiday): JsonResponse
    {
        $this->authorize('update', $holiday);

        return $this->success(
            new HolidayResource($this->holidayService->update($holiday, $request->validated())),
            'Holiday updated successfully.'
        );
    }

    public function destroy(Holiday $holiday): JsonResponse
    {
        $this->authorize('delete', $holiday);

        $this->holidayService->delete($holiday);

        return $this->success(null, 'Holiday deleted successfully.');
    }

    public function import(ImportHolidayRequest $request): JsonResponse
    {
        $this->authorize('create', Holiday::class);

        $count = $this->holidayService->import($request->file('file'));

        return $this->success(['imported' => $count], 'Holidays imported successfully.', 201);
    }

    public function downloadTemplate(): BinaryFileResponse
    {
        $this->authorize('viewAny', Holiday::class);

        return response()->download(
            resource_path('templates/holiday-import-template.xlsx'),
            'holiday-import-template.xlsx',
        );
    }
}
