<?php

namespace App\Http\Services;

use App\Filters\UnitFilter;
use App\Models\Unit;
use Illuminate\Pagination\LengthAwarePaginator;

class UnitService
{
    public function getAllUnits(array $filters): LengthAwarePaginator
    {
        $query = (new UnitFilter(Unit::query(), $filters))->applyFilters();

        return $query->orderBy('number')
            ->paginate((int) data_get($filters, 'per_page', 15));
    }

    public function createUnit(array $data): Unit
    {
        return Unit::query()->create($data);
    }

    public function updateUnit(Unit $unit, array $data): Unit
    {
        $unit->update($data);

        return $unit->fresh();
    }
}
