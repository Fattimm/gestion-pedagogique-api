<?php

namespace App\Repositories;

use App\Models\Classe;
use App\Repositories\Interfaces\ClasseRepositoryInterface;

class ClasseRepository implements ClasseRepositoryInterface
{
    public function all()
    {
        return Classe::all();
    }

    public function find(int $id)
    {
        return Classe::findOrFail($id);
    }

    public function create(array $data)
    {
        return Classe::create($data);
    }

    public function update(int $id, array $data)
    {
        $classe = Classe::findOrFail($id);
        $classe->update($data);
        return $classe;
    }

    public function delete(int $id)
    {
        $classe = Classe::findOrFail($id);
        return $classe->delete();
    }
}
