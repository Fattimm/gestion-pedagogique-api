<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Cours extends Model
{
    use SoftDeletes, HasFactory;

    protected $table = 'cours';

    protected $fillable = [
        'semestre_id',
        'module_id',
        'professeur_id',
        'quota_horaire_global',
        'statut',
    ];

    public function semestre()
    {
        return $this->belongsTo(Semestre::class);
    }

    public function module()
    {
        return $this->belongsTo(Module::class);
    }

    public function professeur()
    {
        return $this->belongsTo(User::class, 'professeur_id');
    }

    public function classes()
    {
        return $this->belongsToMany(Classe::class, 'cours_classe');
    }

    public function sessions()
    {
        return $this->hasMany(SessionDeCours::class);
    }

    public function heuresEffectuees(): float
    {
        return (float) $this->sessions()
            ->where('statut', 'effectuee')
            ->sum('nbre_heure');
    }

    public function heuresPlanifiees(): float
    {
        return (float) $this->sessions()
            ->whereIn('statut', ['planifiee', 'effectuee'])
            ->sum('nbre_heure');
    }
}
