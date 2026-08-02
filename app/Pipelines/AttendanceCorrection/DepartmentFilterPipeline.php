<?php

namespace App\Pipelines\AttendanceCorrection;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class DepartmentFilterPipeline
{
    public function __construct(private readonly ?string $departmentId) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->departmentId) {
            $query->whereHas('attendance.employee', fn (Builder $employeeQuery) => $employeeQuery->where('department_id', $this->departmentId));
        }

        return $next($query);
    }
}
