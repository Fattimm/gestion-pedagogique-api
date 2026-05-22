<?php

namespace App\Repositories;

use App\Models\Salle;
use App\Repositories\Interfaces\SalleRepositoryInterface;

class SalleRepository implements SalleRepositoryInterface
{
    public function all()
    {
        return Salle::all();
    }

    public function find(int $id)
    {
        return Salle::findOrFail($id);
    }

    public function create(array $data)
    {
        return Salle::create($data);
    }

    public function update(int $id, array $data)
    {
        $salle = Salle::findOrFail($id);
        $salle->update($data);
        return $salle;
    }

    public function delete(int $id)
    {
        $salle = Salle::findOrFail($id);
        return $salle->delete();
    }
}
