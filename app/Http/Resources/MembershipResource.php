<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MembershipResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'role' => $this->role,
            'is_active' => $this->is_active,
            'condominium' => $this->whenLoaded('condominium', fn () => [
                'id' => $this->condominium->id,
                'name' => $this->condominium->name,
            ]),
            'unit' => $this->whenLoaded('unit', fn () => $this->unit ? [
                'id' => $this->unit->id,
                'number' => $this->unit->number,
                'floor' => $this->unit->floor,
            ] : null),
        ];
    }
}
