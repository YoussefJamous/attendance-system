<?php

namespace App\Services;

use App\Models\Department;
use App\Pipelines\Department\SearchPipeline;
use App\Pipelines\Department\SortPipeline;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pipeline\Pipeline;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DepartmentService
{
    public function index(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = app(Pipeline::class)
            ->send(Department::query()->withCount('employees'))
            ->through([
                new SearchPipeline($filters['search'] ?? null),
                new SortPipeline($filters['sort_by'] ?? 'name', $filters['direction'] ?? 'asc',),
            ])
            ->thenReturn();

        return $query->paginate($perPage)->appends($filters);
    }

    public function show(Department $department): Department
    {
        return $department->loadCount('employees');
    }

    public function store(array $data): Department
    {
        return DB::transaction(function () use ($data) {
            return Department::create($data);
        });
    }

    public function update(Department $department, array $data): Department
    {
        return DB::transaction(function () use ($department, $data) {
            $department->update($data);
            return $department->refresh();
        });
    }

    public function destroy(Department $department): void
    {
        DB::transaction(function () use ($department) {
            $department->delete();
        });
    }
}
