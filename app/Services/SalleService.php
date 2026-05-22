<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\SalleRepositoryInterface;
use App\Services\Interfaces\SalleServiceInterface;

class SalleService implements SalleServiceInterface
{
    public function __construct(
        protected SalleRepositoryInterface $repository
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
            $salle = $this->repository->create($data);
            DB::commit();
            return ['status' => 201, 'data' => $salle, 'message' => 'Salle créée avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function modifier(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $salle = $this->repository->update($id, $data);
            DB::commit();
            return ['status' => 200, 'data' => $salle, 'message' => 'Salle modifiée avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function supprimer(int $id)
    {
        try {
            $this->repository->delete($id);
            return ['status' => 200, 'message' => 'Salle supprimée avec succès'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }
}
