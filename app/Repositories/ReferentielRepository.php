<?php

namespace App\Repositories;

use App\Models\Referentiel;
use App\Enums\StatutReferentiel;

class ReferentielRepository
{
    public function getAll()
    {
        return Referentiel::all();
    }

    public function findById($id)
    {
        return Referentiel::find($id);
    }

    public function softDelete($id)
    {
        $referentiel = Referentiel::find($id);
        if ($referentiel) {
            $referentiel->statut = StatutReferentiel::ARCHIVER->value; // Soft delete
            $referentiel->save();
        }
    }

    // Ajoutez d'autres méthodes selon vos besoins...
}
