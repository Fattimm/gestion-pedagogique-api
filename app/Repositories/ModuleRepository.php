<?php

namespace App\Repositories;

use App\Models\Module;
use App\Repositories\Interfaces\ModuleRepositoryInterface;

class ModuleRepository implements ModuleRepositoryInterface
{
    public function all()
    {
        return Module::all();
    }

    public function find(int $id)
    {
        return Module::findOrFail($id);
    }

    public function create(array $data)
    {
        return Module::create($data);
    }

    public function update(int $id, array $data)
    {
        $module = Module::findOrFail($id);
        $module->update($data);
        return $module;
    }

    public function delete(int $id)
    {
        $module = Module::findOrFail($id);
        return $module->delete();
    }
}
