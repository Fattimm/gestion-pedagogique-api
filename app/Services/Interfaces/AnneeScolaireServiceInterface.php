<?php

namespace App\Services\Interfaces;

interface AnneeScolaireServiceInterface
{
    public function lister();
    public function trouver(int $id);
    public function creer(array $data);
    public function modifier(int $id, array $data);
    public function supprimer(int $id);
    public function planifierClasse(int $anneeId, int $classeId);
    public function retirerClasse(int $anneeId, int $classeId);
    public function classesPlanifiees(int $anneeId);
}
