<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\File;

class StoreCommonAreaRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string',
            'booking_rules' => 'sometimes|array',
            //'photo' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg', TODO: adicionar validação quando integrar com gcp
            'condominium_id' => 'required|exists:condominiums,id',
        ];
    }
}
