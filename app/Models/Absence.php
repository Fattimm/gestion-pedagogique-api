<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Absence extends Model
{
    use HasFactory;

    protected $fillable = [
        'etudiant_id',
        'session_de_cours_id',
        'nbre_heure',
        'date',
        'justification_date',
        'justification_motif',
        'statut_justification',
        'traite_par',
        'traite_at',
    ];

    protected $casts = [
        'date'               => 'date',
        'justification_date' => 'date',
        'traite_at'          => 'datetime',
    ];

    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function session()
    {
        return $this->belongsTo(SessionDeCours::class, 'session_de_cours_id');
    }

    public function traitePar()
    {
        return $this->belongsTo(User::class, 'traite_par');
    }
}
