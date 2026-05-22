<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Emargement extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_de_cours_id',
        'etudiant_id',
        'signe_a',
        'statut',
        'valide_par',
    ];

    protected $casts = [
        'signe_a' => 'datetime',
    ];

    public function session()
    {
        return $this->belongsTo(SessionDeCours::class, 'session_de_cours_id');
    }

    public function etudiant()
    {
        return $this->belongsTo(User::class, 'etudiant_id');
    }

    public function validePar()
    {
        return $this->belongsTo(User::class, 'valide_par');
    }
}
