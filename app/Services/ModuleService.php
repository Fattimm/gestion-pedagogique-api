<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ModuleRepositoryInterface;
use App\Services\Interfaces\ModuleServiceInterface;

class ModuleService implements ModuleServiceInterface
{
    public function __construct(
        protected ModuleRepositoryInterface $repository
    ) {}

    public function lister()
    {
        return $this->repository->all();
    }

    public function trouver(int $id)
    {
        return $this->repository->find($id);
    }

    public function creer(array $data)
    {
        DB::beginTransaction();
        try {
            $module = $this->repository->create($data);
            DB::commit();
            return ['status' => 201, 'data' => $module, 'message' => 'Module créé avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function modifier(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $module = $this->repository->update($id, $data);
            DB::commit();
            return ['status' => 200, 'data' => $module, 'message' => 'Module modifié avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function supprimer(int $id)
    {
        try {
            $this->repository->delete($id);
            return ['status' => 200, 'message' => 'Module supprimé avec succès'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }
}
