<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class StoreUnitRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'condominium_id' => 'required|exists:condominiums,id',
            'block_id' => 'required|exists:blocks,id',
            'number' => 'required|string', //TODO: unique baseado no condominium e block id
            'floor' => 'required|string',
        ];
    }
}
