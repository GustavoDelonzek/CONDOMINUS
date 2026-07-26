<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class UpdateOccurrenceRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:open,in_progress,resolved,closed',
            'priority' => 'sometimes|string|in:low,medium,high',
            'admin_response' => 'sometimes|nullable|string',
            'notify_resident' => 'sometimes|boolean',
        ];
    }
}
