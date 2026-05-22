<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAnneeScolaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'libelle'    => 'required|string|unique:annees_scolaires,libelle',
            'date_debut' => 'required|date',
            'date_fin'   => 'required|date|after:date_debut',
            'etat'       => 'sometimes|in:planifiee,en_cours,terminee',
        ];
    }

    public function messages(): array
    {
        return [
            'libelle.unique'       => 'Une année scolaire avec ce libellé existe déjà.',
            'date_fin.after'       => 'La date de fin doit être postérieure à la date de début.',
        ];
    }
}
