<?php

namespace App\Models;

use App\Traits\FirebaseSync;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;


class User extends Authenticatable
{
    use SoftDeletes, HasFactory, HasApiTokens; 
    // FirebaseSync;

    // Définir les attributs qui peuvent être massivement assignés
    protected $fillable = [
        'nom',
        'prenom',
        'adresse',
        'telephone',
        'fonction',
        'specialite',
        'grade',
        'email',
        'photo',
        'statut',
        'password',
        'login',
        'role',
        'firebase_id',
    ];

    // Cacher certains attributs lors de la sérialisation du modèle
    protected $hidden = [
        'password',
        'remember_token',
    ];

    // Indiquer les colonnes qui doivent être traitées comme des dates
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
