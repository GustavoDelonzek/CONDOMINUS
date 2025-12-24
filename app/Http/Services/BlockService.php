<?php

namespace App\Http\Services;

use App\Filters\BlockFilter;
use App\Models\Block;
use Illuminate\Pagination\LengthAwarePaginator;

class BlockService
{
    public function getAllBlocksByCondominiumId(array $filters): LengthAwarePaginator
    {
        $query = (new BlockFilter(Block::query(), $filters))->applyFilters();

        return $query->where('condominium_id', data_get($filters, 'condominium_id'))
            ->orderBy('name', 'asc')
            ->paginate((int) data_get($filters, 'per_page', 15));
    }

    public function createBlockInCondominium(array $data): Block
    {
        return Block::query()->create($data);
    }

    public function updateBlock(Block $block, array $data): Block
    {
        $block->update($data);

        return $block->fresh();
    }
}
