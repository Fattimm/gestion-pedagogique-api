<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClasseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle' => 'required|string|max:100',
            'filiere' => 'required|string|max:100',
            'niveau'  => 'required|string|max:50',
        ];
    }
}
