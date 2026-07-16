<?php

namespace App\Pipelines\Department;

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
            'code' => $query->orderBy('code', $this->direction),
            'employees_count' => $query->orderBy('employees_count', $this->direction),
            default => $query->orderBy('name', $this->direction),
        };

        return $next($query);
    }
}
