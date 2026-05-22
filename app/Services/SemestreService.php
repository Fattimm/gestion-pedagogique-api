<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\DB;
use App\Repositories\Interfaces\SemestreRepositoryInterface;
use App\Services\Interfaces\SemestreServiceInterface;

class SemestreService implements SemestreServiceInterface
{
    public function __construct(
        protected SemestreRepositoryInterface $repository
    ) {}

    public function lister()
    {
        return $this->repository->all();
    }

    public function trouver(int $id)
    {
        return $this->repository->find($id);
    }

    public function listerParAnnee(int $anneeId)
    {
        return $this->repository->findByAnnee($anneeId);
    }

    public function creer(array $data)
    {
        DB::beginTransaction();
        try {
            $semestre = $this->repository->create($data);
            DB::commit();
            return ['status' => 201, 'data' => $semestre->load('anneeScolaire'), 'message' => 'Semestre créé avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function modifier(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $semestre = $this->repository->update($id, $data);
            DB::commit();
            return ['status' => 200, 'data' => $semestre, 'message' => 'Semestre modifié avec succès'];
        } catch (Exception $e) {
            DB::rollBack();
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }

    public function supprimer(int $id)
    {
        try {
            $this->repository->delete($id);
            return ['status' => 200, 'message' => 'Semestre supprimé avec succès'];
        } catch (Exception $e) {
            return ['status' => 500, 'message' => $e->getMessage()];
        }
    }
}
