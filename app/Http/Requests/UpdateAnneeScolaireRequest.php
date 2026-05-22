<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAnneeScolaireRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'libelle'    => "sometimes|string|unique:annees_scolaires,libelle,{$id}",
            'date_debut' => 'sometimes|date',
            'date_fin'   => 'sometimes|date|after:date_debut',
            'etat'       => 'sometimes|in:planifiee,en_cours,terminee',
        ];
    }
}
