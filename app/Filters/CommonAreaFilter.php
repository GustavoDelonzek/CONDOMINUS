<?php

namespace App\Filters;

use App\Filters\AbstractQueryFilters;

class CommonAreaFilter extends AbstractQueryFilters
{
    public function search(string $value)
    {
        $this->query->where('name', 'like', '%' . $value . '%');
    }
}
