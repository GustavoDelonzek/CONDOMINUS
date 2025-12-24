<?php

namespace App\Filters;

use App\Filters\AbstractQueryFilters;

class UnitFilter extends AbstractQueryFilters
{
    public function number(string $number)
    {
        $this->query->where('number', 'ilike', '%' . $number . '%');
    }

    public function floor(string $floor)
    {
        $this->query->where('floor', 'ilike', '%' . $floor . '%');
    }

    public function condominiumId(string $condominiumId)
    {
        $this->query->where('condominium_id', $condominiumId);
    }

    public function blockId(string $blockId)
    {
        $this->query->where('block_id', $blockId);
    }

}
