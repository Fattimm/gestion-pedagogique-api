<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SessionDeCours extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'session_de_cours';

    protected $fillable = [
        'cours_id',
        'salle_id',
        'date',
        'heure_debut',
        'heure_fin',
        'nbre_heure',
        'type',
        'statut',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function cours()
    {
        return $this->belongsTo(Cours::class);
    }

    public function salle()
    {
        return $this->belongsTo(Salle::class);
    }
}
