<?php

namespace App\Pipelines\Attendance;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class EmployeeNameFilterPipeline
{
    public function __construct(private readonly ?string $employeeName) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->employeeName) {
            $query->whereHas('employee', function (Builder $employeeQuery) {
                foreach (preg_split('/\s+/', trim($this->employeeName)) as $namePart) {
                    $employeeQuery->where(function (Builder $query) use ($namePart) {
                        $query->where('first_name', 'like', "%{$namePart}%")
                            ->orWhere('last_name', 'like', "%{$namePart}%");
                    });
                }
            });
        }

        return $next($query);
    }
}
