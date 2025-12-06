<?php

namespace App\Filters;

class AdminCompanyFilter extends AbstractQueryFilters
{
    public function name(string $name): void
    {
        $this->query->where('name', 'ilike',  '%' . $name . '%');
    }

    public function cnpj(string $cnpj): void
    {
        $this->query->where('document_cnpj', 'ilike',  '%' . $cnpj . '%');
    }
}
