<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSemestreRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'annee_scolaire_id' => 'required|integer|exists:annees_scolaires,id',
            'libelle'           => 'required|string|max:100',
            'date_debut'        => 'required|date',
            'date_fin'          => 'required|date|after:date_debut',
        ];
    }

    public function messages(): array
    {
        return [
            'annee_scolaire_id.exists' => "L'année scolaire spécifiée n'existe pas.",
            'date_fin.after'           => 'La date de fin doit être postérieure à la date de début.',
        ];
    }
}
