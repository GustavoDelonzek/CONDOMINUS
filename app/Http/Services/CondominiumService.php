<?php

namespace App\Http\Services;

use App\Filters\CondominiumFilter;
use App\Models\Condominium;
use Illuminate\Pagination\LengthAwarePaginator;

class CondominiumService
{
    public function getCondominiums(array $filters): LengthAwarePaginator
    {
        $query = (new CondominiumFilter(Condominium::query(), $filters))->applyFilters();
        $query->orderBy('created_at', 'desc');

        return $query->paginate((int) data_get($filters, 'perPage', 15));
    }

    public function store(array $data): Condominium
    {
        return Condominium::create($data);
    }

    public function updateCondominium(Condominium $condominium, array $data): Condominium
    {
        $condominium->update($data);

        return $condominium->fresh();
    }
}

