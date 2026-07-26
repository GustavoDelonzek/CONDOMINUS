<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class FilterOccurrenceRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'sometimes|string|in:open,in_progress,resolved,closed',
            'priority' => 'sometimes|string|in:low,medium,high',
            'per_page' => 'sometimes|integer|min:1',
        ];
    }
}
