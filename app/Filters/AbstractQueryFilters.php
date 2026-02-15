<?php

namespace App\Filters;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class AbstractQueryFilters
{
    protected array $searchableColumns = [];

    public function __construct(
        protected Builder $query,
        protected array $filters = [],
    ) {}

    public function applyFilters(): Builder
    {
        foreach ($this->filters as $key => $value) {
            if (is_null($value) || $value === '') {
                continue;
            }

            $methodName = Str::camel($key);

            if (method_exists($this, $methodName)) {
                $this->$methodName($value);
            }
        }

        return $this->query;
    }

    public function search(string $value)
    {
        foreach ($this->searchableColumns as $searchableValue) {
            if (Str::contains($searchableValue, $value)) {
                $this->query->where($searchableValue, 'like', "%{$value}%");
            }
        }
    }
}
