<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReviewContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'note_veracite' => ['required', 'integer', 'min:1', 'max:5'],
            'is_valid' => ['required', 'boolean'],
            'commentaire' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
