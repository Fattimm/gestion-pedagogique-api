<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Rules\CustumPasswordRule;

class StoreUserRequest extends FormRequest
{
    /**
     * Détermine si l'utilisateur est autorisé à faire cette demande.
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Règles de validation qui s'appliquent à la requête.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'nom' => 'required|string|max:255',
            'prenom' => 'required|string|max:255',
            'login' => 'required|string|unique:users,login|max:255',
            'password' => [
                'required',
                'string',
                'min:5',
                'regex:/[a-z]/', 
                'regex:/[A-Z]/', 
                'regex:/[0-9]/', 
                'regex:/[@$!%*?&]/', 
                'confirmed',
                new CustumPasswordRule(),
            ],
            'adresse' => 'nullable|string|max:255',
            'telephone' => 'required|string|unique:users,telephone|max:15', // Validation pour numéro de téléphone
            'fonction' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email|max:255',
            'photo' => 'sometimes|nullable|image|mimes:jpg,jpeg,png|max:2048',
            'statut' => 'in:actif,inactif',
            'role' => 'required|in:ADMIN,COACH,MANAGER,CM,APPRENANT',

        ];
    }

    /**
     * Messages d'erreur personnalisés pour la validation.
     *
     * @return array<string, string>
     */
    public function messages()
    {
        return [
            'nom.required' => 'Le nom est obligatoire.',
            'prenom.required' => 'Le prénom est obligatoire.',
            'login.required' => 'Le login est obligatoire.',
            'login.unique' => 'Ce login est déjà utilisé.',
            'login.max' => 'Le login ne doit pas dépasser 255 caractères.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 5 caractères.',
            'password.regex' => 'Le mot de passe doit contenir au moins une lettre majuscule, une lettre minuscule, un chiffre, et un caractère spécial (@$!%*?&).',
            'email.required' => 'L\'adresse e-mail est obligatoire.',
            'email.email' => 'Veuillez fournir une adresse e-mail valide.',
            'email.unique' => 'Cette adresse e-mail est déjà utilisée.',
            'email.max' => 'L\'adresse e-mail ne doit pas dépasser 255 caractères.',
            'adresse.max' => 'L\'adresse ne doit pas dépasser 255 caractères.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'telephone.max' => 'Le numéro de téléphone ne doit pas dépasser 15 caractères.',
            'fonction.required' => 'La fonction n"existe pas.',
            'photo.image' => 'Le fichier doit être une image.',
            'photo.mimes' => 'L\'image doit être au format jpg, jpeg, ou png.',
            'photo.max' => 'L\'image ne doit pas dépasser 2048 Ko.',
            'statut.in' => 'Le statut doit être "actif" ou "inactif".',
            'role' => 'le role est Obligatoire',
            
        ];
    }

    /**
     * Gérer une tentative de validation échouée.
     *
     * @param \Illuminate\Contracts\Validation\Validator $validator
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 422,
            'data' => $validator->errors(),
            'message' => 'Erreur de validation des données fournies.',
        ], 422));
    }
}
