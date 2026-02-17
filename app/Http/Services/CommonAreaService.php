<?php

namespace App\Http\Services;

use App\Filters\CommonAreaFilter;
use App\Http\Enums\EnumRoleUser;
use App\Models\CommonArea;
use App\Models\Membership;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class CommonAreaService
{
    public function getAllCommonAreas(array $filters): LengthAwarePaginator
    {
        $condoId = data_get($filters, 'membership.condominium_id');

        $query = (new CommonAreaFilter(CommonArea::query()->where('condominium_id', $condoId)))->applyFilters();

        return $query->paginate((int) data_get($filters, 'per_page', 15));
    }

    public function store(array $data)
    {
        $membership = data_get($data, 'membership');

        if ($membership->condominium_id !== $data['condominium_id'] || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        return CommonArea::create(collect($data)->except(['membership'])->toArray());
    }

    public function update(CommonArea $commonArea, array $data): CommonArea
    {
        $membership = data_get($data, 'membership');

        if ($membership->condominium_id !== $commonArea->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $commonArea->update(collect($data)->except(['membership'])->toArray());

        return $commonArea->fresh();
    }

    public function show(CommonArea $commonArea, Membership $membership): CommonArea
    {
        if ($commonArea->condominium_id !== $membership->condominium_id) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        return $commonArea;
    }

    public function destroy(CommonArea $commonArea, Membership $membership): bool
    {
        if ($commonArea->condominium_id !== $membership->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        return $commonArea->delete();
    }
}
