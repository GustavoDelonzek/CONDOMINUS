<?php

namespace App\Filters;

class CondominiumFilter extends AbstractQueryFilters
{
    public function name(string $name): void
    {
        $this->query->where('name', 'ilike', '%' . $name . '%');
    }

    public function address(string $address): void
    {
        $this->query->where('address_full', 'ilike', '%' . $address . '%');
    }
}
