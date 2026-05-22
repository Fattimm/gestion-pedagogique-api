<?php

namespace App\Services\Interfaces;

interface CoursServiceInterface
{
    public function lister(array $filters = []);
    public function trouver(int $id);
    public function creer(array $data, array $classeIds);
    public function modifier(int $id, array $data, ?array $classeIds = null);
    public function supprimer(int $id);
}
