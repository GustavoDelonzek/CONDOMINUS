<?php

namespace App\Traits;

trait HasCondominiumContext
{
    public function validated($key = null, $default = null): array
    {
        $validated = parent::validated($key, $default);

        if ($this->current_membership) {
            $validated['membership'] = $this->current_membership;
        }

        return $validated;
    }
}
