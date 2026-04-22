<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AnnotateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'reponse_choisie' => ['required', 'string', 'max:255'],
            'temps_execution_ms' => ['required', 'integer', 'min:0'],
        ];
    }
}
