<?php

namespace App\Pipelines\Holiday;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class MonthFilterPipeline
{
    public function __construct(
        private readonly ?string $month,
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->month) {
            $month = Carbon::createFromFormat('Y-m', $this->month);

            $query
                ->whereDate('start_date', '<=', $month->endOfMonth()->toDateString())
                ->whereDate('end_date', '>=', $month->startOfMonth()->toDateString());
        }

        return $next($query);
    }
}
