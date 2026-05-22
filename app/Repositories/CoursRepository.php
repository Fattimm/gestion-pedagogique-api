<?php

namespace App\Repositories;

use App\Models\Cours;
use App\Repositories\Interfaces\CoursRepositoryInterface;

class CoursRepository implements CoursRepositoryInterface
{
    public function all(array $filters = [])
    {
        $query = Cours::with(['semestre', 'module', 'professeur', 'classes', 'sessions']);

        if (isset($filters['statut'])) {
            $query->where('statut', $filters['statut']);
        }

        return $query->get();
    }

    public function find(int $id)
    {
        return Cours::with(['semestre', 'module', 'professeur', 'classes', 'sessions'])->findOrFail($id);
    }

    public function create(array $data)
    {
        return Cours::create($data);
    }

    public function update(int $id, array $data)
    {
        $cours = Cours::findOrFail($id);
        $cours->update($data);
        return $cours;
    }

    public function delete(int $id)
    {
        $cours = Cours::findOrFail($id);
        return $cours->delete();
    }

    public function attachClasses(int $coursId, array $classeIds): void
    {
        $cours = Cours::findOrFail($coursId);
        $cours->classes()->sync($classeIds);
    }
}
