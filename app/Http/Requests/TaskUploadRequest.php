<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskUploadRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'images' => ['required', 'array', 'min:1'],
            'images.*' => ['required', 'image', 'max:8192'],
            'type_tache' => ['required', 'string', 'max:100'],
            'question' => ['required', 'string'],
            'options' => ['nullable', 'array'],
        ];
    }
}
