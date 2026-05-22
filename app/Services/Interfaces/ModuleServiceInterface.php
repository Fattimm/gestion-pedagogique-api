<?php

namespace App\Services\Interfaces;

interface ModuleServiceInterface
{
    public function lister();
    public function trouver(int $id);
    public function creer(array $data);
    public function modifier(int $id, array $data);
    public function supprimer(int $id);
}
