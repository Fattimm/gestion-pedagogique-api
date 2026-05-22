<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\ClasseRepositoryInterface;
use App\Services\Interfaces\ClasseServiceInterface;

class ClasseService implements ClasseServiceInterface
{
    public function __construct(
        protected ClasseRepositoryInterface $repository
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
            $classe = $this->repository->create($data);
            DB::commit();
            return ['status' => 201, 'data' => $classe, 'message' => 'Classe créée avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function modifier(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $classe = $this->repository->update($id, $data);
            DB::commit();
            return ['status' => 200, 'data' => $classe, 'message' => 'Classe modifiée avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function supprimer(int $id)
    {
        try {
            $this->repository->delete($id);
            return ['status' => 200, 'message' => 'Classe supprimée avec succès'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }
}
