<?php

namespace App\Http\Services;

use App\Filters\BlockFilter;
use App\Http\Enums\EnumRoleUser;
use App\Models\Block;
use App\Models\Membership;
use Illuminate\Pagination\LengthAwarePaginator;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class BlockService
{
    public function getAllBlocksByCondominiumId(array $filters): LengthAwarePaginator
    {
        $membership = data_get($filters, 'membership');

        if ($membership->condominium_id !== $filters['condominium_id']) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $query = (new BlockFilter(Block::query()->where('condominium_id', $membership->condominium_id), $filters))->applyFilters();

        return $query->orderBy('created_at', 'desc')
            ->paginate((int) data_get($filters, 'per_page', 15));
    }

    public function createBlockInCondominium(array $data): Block
    {
        $membership = data_get($data, 'membership');

        if ($membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $data['condominium_id'] = $membership->condominium_id;

        return Block::query()->create(collect($data)->except('membership')->toArray());
    }

    public function updateBlock(Block $block, array $data): Block
    {
        $membership = data_get($data, 'membership');

        if ($membership->condominium_id !== $block->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $block->update(collect($data)->except('membership')->toArray());

        return $block->fresh();
    }

    public function showBlock(Block $block, Membership $membership): Block
    {
        if ($block->condominium_id !== $membership->condominium_id) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        return $block;
    }

    public function deleteBlock(Block $block, Membership $membership): void
    {
        if ($block->condominium_id !== $membership->condominium_id || $membership->role !== EnumRoleUser::SYNDIC->value) {
            throw new AccessDeniedHttpException('Access denied for this action.');
        }

        $block->delete();
    }
}
