<?php

namespace App\Pipelines\Attendance;

use App\Models\Employee;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SortPipeline
{
    public function __construct(
        private readonly string $sort = 'attendance_date',
        private readonly string $direction = 'desc',
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->sort === 'employee_name') {
            $query->orderBy(
                Employee::query()
                    ->select('first_name')
                    ->whereColumn('employees.id', 'attendances.employee_id'),
                $this->direction,
            );
            $query->orderBy(
                Employee::query()
                    ->select('last_name')
                    ->whereColumn('employees.id', 'attendances.employee_id'),
                $this->direction,
            );
        } else {
            $query->orderBy($this->sort, $this->direction);
        }

        return $next($query);
    }
}
