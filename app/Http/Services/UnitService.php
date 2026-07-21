<?php

namespace App\Http\Services;

use App\Filters\UnitFilter;
use App\Http\Enums\EnumRoleUser;
use App\Models\Membership;
use App\Models\Unit;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class UnitService
{
    public function getAllUnits(array $filters): LengthAwarePaginator
    {
        $membership = data_get($filters, 'membership');

        if ($membership->condominium_id !== $filters['condominium_id']) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $query = (new UnitFilter(Unit::query()->where('condominium_id', $membership->condominium_id), $filters))->applyFilters();

        return $query->orderBy('number')
            ->paginate((int) data_get($filters, 'per_page', 15));
    }

    public function createUnit(array $data): Unit
    {
        $membership = data_get($data, 'membership');

        if ($membership->condominium_id !== $data['condominium_id'] || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        return Unit::query()->create(collect($data)->except('membership')->toArray());
    }

    public function updateUnit(Unit $unit, array $data): Unit
    {
        $membership = data_get($data, 'membership');

        if ($membership->condominium_id !== $unit->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $unit->update(collect($data)->except('membership')->toArray());

        return $unit->fresh();
    }

    public function deleteUnit(Unit $unit, Membership $membership): void
    {
        if ($membership->condominium_id !== $unit->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $unit->delete();
    }

    public function showUnit(Unit $unit, Membership $membership): Unit
    {
        if ($membership->condominium_id !== $unit->condominium_id) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        return $unit;
    }
}
