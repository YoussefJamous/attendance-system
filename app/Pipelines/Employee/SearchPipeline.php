<?php

namespace App\Pipelines\Employee;

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
                $query->whereRaw(
                    "CONCAT(first_name, ' ', last_name) LIKE ?",
                    ["%{$this->search}%"]
                )
                    ->orWhereHas('user', function (Builder $query) {
                        $query->where('email', 'like', "%{$this->search}%");
                    });
            });
        }

        return $next($query);
    }
}
