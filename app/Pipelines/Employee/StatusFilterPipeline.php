<?php

namespace App\Pipelines\Employee;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class StatusFilterPipeline
{
    public function __construct(
        private readonly ?string $status
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->status) {
            $query->where('status', $this->status);
        }

        return $next($query);
    }
}
