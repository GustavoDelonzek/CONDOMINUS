<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class FilterReservationRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|string|in:pending,confirmed,canceled,completed,denied',
            'common_area_id' => 'sometimes|string|exists:common_areas,id',
            'per_page' => 'sometimes|integer|min:1',
        ];
    }
}
