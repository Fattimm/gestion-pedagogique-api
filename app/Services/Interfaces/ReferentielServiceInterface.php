<?php

namespace App\Services\Interfaces;

interface ReferentielServiceInterface
{
    public function create(array $data);
    // public function update($id, array $data);
    public function softDelete($id);
    
}
