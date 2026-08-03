<?php

namespace App\Pipelines\Attendance;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class AttendanceDateFilterPipeline
{
    public function __construct(
        private readonly ?string $attendanceDate,
        private readonly ?string $dateFrom,
        private readonly ?string $dateTo,
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        $query
            ->when($this->attendanceDate, fn (Builder $query) => $query->whereDate('attendance_date', $this->attendanceDate))
            ->when($this->dateFrom, fn (Builder $query) => $query->whereDate('attendance_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn (Builder $query) => $query->whereDate('attendance_date', '<=', $this->dateTo));

        return $next($query);
    }
}
