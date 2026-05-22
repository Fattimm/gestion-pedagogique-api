<?php

namespace App\Services\Interfaces;

interface SemestreServiceInterface
{
    public function lister();
    public function trouver(int $id);
    public function listerParAnnee(int $anneeId);
    public function creer(array $data);
    public function modifier(int $id, array $data);
    public function supprimer(int $id);
}
