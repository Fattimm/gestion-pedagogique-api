<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'     => 'required|string|unique:modules,libelle',
            'description' => 'nullable|string',
        ];
    }

    public function messages(): array
    {
        return [
            'libelle.unique' => 'Un module avec ce libellé existe déjà.',
        ];
    }
}
