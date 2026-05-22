<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Classe extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'libelle',
        'filiere',
        'niveau',
    ];

    public function anneesScolaires()
    {
        return $this->belongsToMany(AnneeScolaire::class, 'annee_scolaire_classe');
    }
}
