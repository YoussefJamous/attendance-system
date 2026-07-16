<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\Department\IndexDepartmentRequest;
use App\Http\Requests\Department\StoreDepartmentRequest;
use App\Http\Requests\Department\UpdateDepartmentRequest;
use App\Http\Resources\DepartmentResource;
use App\Models\Department;
use App\Services\DepartmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DepartmentController extends ApiController
{
    public function __construct(private readonly DepartmentService $departmentService) {}

    public function index(IndexDepartmentRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Department::class);

        $departments = $this->departmentService->index($request->validated(), $request->integer('per_page', $this->per_page),);

        return $this->success(
            [
                'departments' => DepartmentResource::collection($departments),
                'pagination' => $this->paginationData($departments),
            ],
            'Departments retrieved successfully.'
        );
    }

    public function store(StoreDepartmentRequest $request): JsonResponse
    {
        $this->authorize('create', Department::class);

        $result = $this->departmentService->store($request->validated());

        return $this->success(new DepartmentResource($result), 'Department created successfully.', 201);
    }

    public function show(Department $department): JsonResponse
    {
        $this->authorize('view', $department);

        $department = $this->departmentService->show($department);

        return $this->success(new DepartmentResource($department), 'Department retrieved successfully.');
    }

    public function update(UpdateDepartmentRequest $request, Department $department): JsonResponse
    {
        $this->authorize('update', $department);

        $department = $this->departmentService->update($department, $request->validated())->loadCount('employees');

        return $this->success(new DepartmentResource($department), 'Department updated successfully.');
    }

    public function destroy(Department $department)
    {
        $this->authorize('destroy', $department);

        $this->departmentService->destroy($department);

        return $this->success(null, 'Department deleted successfully.');
    }
}
