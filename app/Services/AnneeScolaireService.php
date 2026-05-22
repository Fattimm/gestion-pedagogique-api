<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\AnneeScolaireRepositoryInterface;
use App\Services\Interfaces\AnneeScolaireServiceInterface;

class AnneeScolaireService implements AnneeScolaireServiceInterface
{
    public function __construct(
        protected AnneeScolaireRepositoryInterface $repository
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
            $annee = $this->repository->create($data);
            DB::commit();
            return ['status' => 201, 'data' => $annee, 'message' => 'Année scolaire créée avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function modifier(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $annee = $this->repository->update($id, $data);
            DB::commit();
            return ['status' => 200, 'data' => $annee, 'message' => 'Année scolaire modifiée avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function supprimer(int $id)
    {
        try {
            $this->repository->delete($id);
            return ['status' => 200, 'message' => 'Année scolaire supprimée avec succès'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function planifierClasse(int $anneeId, int $classeId)
    {
        try {
            $this->repository->attachClasse($anneeId, $classeId);
            return ['status' => 200, 'message' => 'Classe planifiée avec succès'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function retirerClasse(int $anneeId, int $classeId)
    {
        try {
            $this->repository->detachClasse($anneeId, $classeId);
            return ['status' => 200, 'message' => 'Classe retirée avec succès'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function classesPlanifiees(int $anneeId)
    {
        return $this->repository->getClassesPlanifiees($anneeId);
    }
}
