<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCommonAreaRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:255',
            'booking_rules' => 'sometimes|array',
            'booking_rules.opens_at' => 'sometimes|nullable|date_format:H:i',
            'booking_rules.closes_at' => 'sometimes|nullable|date_format:H:i',
            'booking_rules.min_advance_hours' => 'sometimes|nullable|integer|min:0',
            'booking_rules.max_duration_hours' => 'sometimes|nullable|integer|min:1',
            'booking_rules.max_reservations_per_unit_per_month' => 'sometimes|nullable|integer|min:1',
            'booking_rules.requires_approval' => 'sometimes|nullable|boolean',
            'booking_rules.fee' => 'sometimes|nullable|numeric|min:0',
            //'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg',
        ];
    }
}
