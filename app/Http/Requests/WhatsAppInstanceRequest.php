<?php

namespace App\Http\Requests;

use App\Traits\HasCondominiumContext;
use Illuminate\Foundation\Http\FormRequest;

class WhatsAppInstanceRequest extends FormRequest
{
    use HasCondominiumContext;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
