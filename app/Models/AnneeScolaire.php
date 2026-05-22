<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AnneeScolaire extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'annees_scolaires';

    protected $fillable = [
        'libelle',
        'date_debut',
        'date_fin',
        'etat',
    ];

    protected $casts = [
        'date_debut' => 'date',
        'date_fin'   => 'date',
    ];

    public function semestres()
    {
        return $this->hasMany(Semestre::class);
    }

    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'annee_scolaire_classe');
    }
}
