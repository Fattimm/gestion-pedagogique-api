<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'semestre_id'          => 'required|integer|exists:semestres,id',
            'module_id'            => 'required|integer|exists:modules,id',
            'professeur_id'        => 'required|integer|exists:users,id',
            'quota_horaire_global' => 'required|numeric|min:1',
            'classes'              => 'required|array|min:1',
            'classes.*'            => 'integer|exists:classes,id',
            'statut'               => 'sometimes|in:planifie,en_cours,termine',
        ];
    }

    public function messages(): array
    {
        return [
            'classes.required' => 'Vous devez associer au moins une classe au cours.',
            'semestre_id.exists' => "Le semestre spécifié n'existe pas.",
            'module_id.exists'   => "Le module spécifié n'existe pas.",
            'professeur_id.exists' => "Le professeur spécifié n'existe pas.",
        ];
    }
}
