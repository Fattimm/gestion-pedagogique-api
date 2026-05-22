<?php

namespace App\Services\Interfaces;

interface SessionDeCoursServiceInterface
{
    public function lister();
    public function trouver(int $id);
    public function listerParCours(int $coursId);
    public function creer(array $data);
    public function modifier(int $id, array $data);
    public function annuler(int $id);
}
