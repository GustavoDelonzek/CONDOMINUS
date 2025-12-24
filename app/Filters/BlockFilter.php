<?php

namespace App\Filters;

use App\Filters\AbstractQueryFilters;

class BlockFilter extends AbstractQueryFilters
{
    public function name(string $name)
    {
        $this->query->where('name', 'like', '%' . $name . '%');
    }
}
