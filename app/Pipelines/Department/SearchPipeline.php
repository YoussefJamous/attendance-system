<?php

namespace App\Pipelines\Department;

use Closure;
use Illuminate\Database\Eloquent\Builder;

class SearchPipeline
{
    public function __construct(
        private readonly ?string $search
    ) {}

    public function handle(Builder $query, Closure $next): Builder
    {
        if ($this->search) {
            $query->where(function (Builder $query) {
                $query->where('name', 'like', "%{$this->search}%")
                    ->orWhere('code', 'like', "%{$this->search}%");
            });
        }

        return $next($query);
    }
}
