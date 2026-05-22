<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSessionDeCoursRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'cours_id'    => 'required|integer|exists:cours,id',
            'date'        => 'required|date|after_or_equal:today',
            'heure_debut' => 'required|date_format:H:i',
            'heure_fin'   => 'required|date_format:H:i|after:heure_debut',
            'nbre_heure'  => 'required|numeric|min:0.5',
            'type'        => 'required|in:presentiel,en_ligne',
            'salle_id'    => 'required_if:type,presentiel|nullable|integer|exists:salles,id',
        ];
    }

    public function messages(): array
    {
        return [
            'heure_fin.after'        => "L'heure de fin doit être après l'heure de début.",
            'salle_id.required_if'   => 'Une salle est obligatoire pour une session en présentiel.',
            'date.after_or_equal'    => 'La date ne peut pas être dans le passé.',
        ];
    }
}
