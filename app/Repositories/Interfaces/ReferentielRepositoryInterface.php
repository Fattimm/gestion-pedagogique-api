<?php

namespace App\Repositories\Interfaces;

use App\Models\Referentiel;
use Illuminate\Database\Eloquent\Collection;

interface ReferentielRepositoryInterface
{
    public function create(array $data): Referentiel;
    public function findByCodeOrLibelle($code, $libelle): ?Referentiel;
    public function softDelete($id);
    public function all(): Collection;
    public function getAllFromFirestore();
    public function findByStatut($etat);
    public function addCompetence($referentielId, $type, array $competenceData);
    public function addModule($referentielId, $type, $competenceId, array $moduleData);
    public function find($id);
    public function getId();
    public function getCompetences($referentielId): array;
    public function getModules($referentielId, $competenceId): array;
    public function update(string $id, array $data): void;
    public function softDeleteCompetence(string $referentielId, string $competenceId): void;
    public function softDeleteModule(string $referentielId, string $competenceId, string $moduleId): void;
    public function getCompetencesByReferentielId($referentId);

}
