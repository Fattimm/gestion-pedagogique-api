<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSalleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom'           => 'required|string|max:100',
            'numero'        => 'required|string|unique:salles,numero',
            'nombre_places' => 'required|integer|min:1',
        ];
    }

    public function messages(): array
    {
        return [
            'numero.unique' => 'Ce numéro de salle existe déjà.',
        ];
    }
}
