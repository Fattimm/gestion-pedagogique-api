<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\User;


class Promo extends FirebaseModel
{
    use SoftDeletes, HasFactory; 


    protected $fillable = [
        'libelle',
        'date_debut',
        'date_fin',
        'duree',
        'etat',
        'photo_couverture'
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin' => 'date',
        'etat' => 'string',
    ];

 

    public function users()
    {
        return $this->belongsToMany(User::class);
        
    }

    public function referentiels()
    {
        return $this->hasMany(Referentiel::class);
    }
    
}