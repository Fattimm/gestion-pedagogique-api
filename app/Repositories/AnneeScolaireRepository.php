<?php

namespace App\Repositories;

use App\Models\AnneeScolaire;
use App\Repositories\Interfaces\AnneeScolaireRepositoryInterface;

class AnneeScolaireRepository implements AnneeScolaireRepositoryInterface
{
    public function all()
    {
        return AnneeScolaire::with(['semestres', 'classes'])->get();
    }

    public function find(int $id)
    {
        return AnneeScolaire::with(['semestres', 'classes'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return AnneeScolaire::create($data);
    }

    public function update(int $id, array $data)
    {
        $annee = AnneeScolaire::findOrFail($id);
        $annee->update($data);
        return $annee;
    }

    public function delete(int $id)
    {
        $annee = AnneeScolaire::findOrFail($id);
        return $annee->delete();
    }

    public function attachClasse(int $anneeId, int $classeId): void
    {
        $annee = AnneeScolaire::findOrFail($anneeId);
        $annee->classes()->syncWithoutDetaching([$classeId]);
    }

    public function detachClasse(int $anneeId, int $classeId): void
    {
        $annee = AnneeScolaire::findOrFail($anneeId);
        $annee->classes()->detach($classeId);
    }

    public function getClassesPlanifiees(int $anneeId)
    {
        $annee = AnneeScolaire::findOrFail($anneeId);
        return $annee->classes;
    }
}
