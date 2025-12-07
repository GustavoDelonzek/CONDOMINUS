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

    public function adminCompanyId(string $adminCompanyId): void
    {
        $this->query->where('admin_company_id', $adminCompanyId);
    }
}
