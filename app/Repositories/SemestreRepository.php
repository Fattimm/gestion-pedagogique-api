<?php

namespace App\Repositories;

use App\Models\Semestre;
use App\Repositories\Interfaces\SemestreRepositoryInterface;

class SemestreRepository implements SemestreRepositoryInterface
{
    public function all()
    {
        return Semestre::with('anneeScolaire')->get();
    }

    public function find(int $id)
    {
        return Semestre::with('anneeScolaire')->findOrFail($id);
    }

    public function findByAnnee(int $anneeId)
    {
        return Semestre::where('annee_scolaire_id', $anneeId)->get();
    }

    public function create(array $data)
    {
        return Semestre::create($data);
    }

    public function update(int $id, array $data)
    {
        $semestre = Semestre::findOrFail($id);
        $semestre->update($data);
        return $semestre;
    }

    public function delete(int $id)
    {
        $semestre = Semestre::findOrFail($id);
        return $semestre->delete();
    }
}
