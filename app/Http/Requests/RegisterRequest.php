<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['nullable', 'string', 'max:255'],
            'nom' => ['nullable', 'string', 'max:255'],
            'telephone' => ['required', 'string', 'max:20', 'unique:users,telephone'],
            'email' => ['nullable', 'email', 'max:255', 'unique:users,email'],
            'password' => ['nullable', 'string', 'required_without:mot_de_passe', Password::min(6)],
            'mot_de_passe' => ['nullable', 'string', 'required_without:password', Password::min(6)],
        ];
    }
}
