<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\CoursRepositoryInterface;
use App\Services\Interfaces\CoursServiceInterface;

class CoursService implements CoursServiceInterface
{
    public function __construct(
        protected CoursRepositoryInterface $repository
    ) {}

    public function lister(array $filters = [])
    {
        return $this->repository->all($filters);
    }

    public function trouver(int $id)
    {
        $cours = $this->repository->find($id);
        $cours->heures_effectuees  = $cours->heuresEffectuees();
        $cours->heures_planifiees  = $cours->heuresPlanifiees();
        $cours->heures_restantes   = max(0, $cours->quota_horaire_global - $cours->heures_planifiees);
        return $cours;
    }

    public function creer(array $data, array $classeIds)
    {
        DB::beginTransaction();
        try {
            $cours = $this->repository->create($data);
            $this->repository->attachClasses($cours->id, $classeIds);
            DB::commit();
            return ['status' => 201, 'data' => $cours->load(['semestre', 'module', 'professeur', 'classes']), 'message' => 'Cours créé avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function modifier(int $id, array $data, ?array $classeIds = null)
    {
        DB::beginTransaction();
        try {
            $cours = $this->repository->update($id, $data);
            if ($classeIds !== null) {
                $this->repository->attachClasses($id, $classeIds);
            }
            DB::commit();
            return ['status' => 200, 'data' => $cours, 'message' => 'Cours modifié avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function supprimer(int $id)
    {
        try {
            $this->repository->delete($id);
            return ['status' => 200, 'message' => 'Cours supprimé avec succès'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }
}
