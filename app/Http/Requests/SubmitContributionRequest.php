<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SubmitContributionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type_contenu' => ['required', 'in:image,texte,audio'],
            'fichier' => ['nullable', 'file', 'max:10240'],
            'texte_contenu' => ['nullable', 'string', 'max:5000'],
            'langue' => ['required', 'in:francais,nouchi,dioula,baoule,bete,autre'],
            'description' => ['required', 'string', 'max:1000'],
            'categorie' => ['nullable', 'string', 'max:100'],
            'metadata' => ['nullable', 'array'],
            'nb_reviews_requises' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }
}
