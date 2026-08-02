<?php

namespace App\Pipelines\AttendanceCorrection;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class EmployeeFilterPipeline
{
    public function __construct(private readonly ?string $employeeId) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->employeeId) {
            $query->whereHas('attendance', fn (Builder $attendanceQuery) => $attendanceQuery->where('employee_id', $this->employeeId));
        }

        return $next($query);
    }
}
