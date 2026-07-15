<?php

namespace App\Pipelines\Employee;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class SortPipeline
{
    public function __construct(
        private readonly string $sort = 'name',
        private readonly string $direction = 'asc',
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        match ($this->sort) {
            'hire_date' => $query->orderBy('hire_date', $this->direction),
            'status' => $query->orderBy('status', $this->direction),
            'gender' => $query->orderBy('gender', $this->direction),
            default => $query->orderBy('first_name', $this->direction)->orderBy('last_name', $this->direction),
        };

        return $next($query);
    }
}
