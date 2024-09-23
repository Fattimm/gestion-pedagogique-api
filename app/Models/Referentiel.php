<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Referentiel extends FirebaseModel
{

    use SoftDeletes, HasFactory; 

    protected $fillable = [
        'code',
        'libelle',
        'description',
        'photo',
        'statut',
        'competences' 
    ];

    protected $firebaseCollection = 'referentiels';

    protected $casts = [
        'statut' => 'string',
    ];


    public function promo()
    {
        return $this->belongsTo(Promo::class);
    }

    public function apprenants()
    {
        return $this->hasMany(User::class)->where('role', 'apprenant');
    }
}