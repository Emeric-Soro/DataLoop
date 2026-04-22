<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'telephone' => ['required', 'string', 'max:20'],
            'password' => ['nullable', 'string', 'required_without:mot_de_passe'],
            'mot_de_passe' => ['nullable', 'string', 'required_without:password'],
        ];
    }
}
