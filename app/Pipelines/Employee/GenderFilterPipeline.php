<?php

namespace App\Pipelines\Employee;

use Closure;
use Illuminate\Database\Eloquent\Builder;


class GenderFilterPipeline
{
    public function __construct(
        private readonly ?string $gender
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->gender) {
            $query->where('gender', $this->gender);
        }

        return $next($query);
    }
}
