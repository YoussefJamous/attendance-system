<?php

namespace App\Http\Controllers\API\V1;

use App\Http\Controllers\API\ApiController;
use App\Http\Requests\Employee\IndexEmployeeRequest;
use App\Http\Requests\Employee\StoreEmployeeRequest;
use App\Http\Requests\Employee\UpdateEmployeeRequest;
use App\Http\Resources\EmployeeCreatedResource;
use App\Http\Resources\EmployeeResource;
use App\Models\Employee;
use App\Services\EmployeeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmployeeController extends ApiController
{
    public function __construct(private readonly EmployeeService $employeeService) {}

    public function index(IndexEmployeeRequest $request): JsonResponse
    {
        $this->authorize('viewAny', Employee::class);

        $employees = $this->employeeService->index(
            $request->validated(),
            $request->integer('per_page', $this->per_page),
        );

        return $this->success(
            [
                'employees' => EmployeeResource::collection($employees),
                'pagination' => $this->paginationData($employees),
            ],
            'Employees retrieved successfully.'
        );
    }

    public function store(StoreEmployeeRequest $request): JsonResponse
    {
        $this->authorize('create', Employee::class);

        $result = $this->employeeService->store(
            $request->validated(),
            $request->file('identity_document'),
        );

        return $this->success(new EmployeeCreatedResource($result), 'Employee created successfully.', 201);
    }

    public function show(Employee $employee): JsonResponse
    {
        $this->authorize('view', $employee);

        $employee = $this->employeeService->show($employee);

        return $this->success(new EmployeeResource($employee), 'Employee retrieved successfully.');
    }

    public function update(UpdateEmployeeRequest $request, Employee $employee): JsonResponse
    {
        $this->authorize('update', $employee);

        $employee = $this->employeeService->update(
            $employee,
            $request->validated(),
            $request->file('identity_document')
        );

        return $this->success(new EmployeeResource($employee), 'Employee updated successfully.');
    }

    public function destroy(Employee $employee): JsonResponse
    {
        $this->authorize('delete', $employee);

        $this->employeeService->delete($employee);

        return $this->success(null, 'Employee deleted successfully.');
    }

    public function restore(Employee $employee): JsonResponse
    {
        $this->authorize('restore', $employee);

        $employee = $this->employeeService->restore($employee);

        return $this->success(new EmployeeResource($employee), 'Employee restored successfully.');
    }
}
