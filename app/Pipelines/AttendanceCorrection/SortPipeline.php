<?php

namespace App\Pipelines\AttendanceCorrection;

use App\Models\Attendance;
use Closure;
use Illuminate\Database\Eloquent\Builder;

class SortPipeline
{
    public function __construct(
        private readonly string $sort = 'created_at',
        private readonly string $direction = 'desc',
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->sort === 'attendance_date') {
            $query->orderBy(
                Attendance::query()
                    ->select('attendance_date')
                    ->whereColumn('attendances.id', 'attendance_corrections.attendance_id'),
                $this->direction,
            );
        } else {
            $query->orderBy($this->sort, $this->direction);
        }

        return $next($query);
    }
}
