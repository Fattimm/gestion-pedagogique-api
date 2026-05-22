<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Inscription;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithValidation;

class EtudiantsImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    public function __construct(
        private int $classeId,
        private int $anneeScolaireId
    ) {}

    public function model(array $row): ?Inscription
    {
        // Colonnes attendues : nom, prenom, email, telephone, login
        $etudiant = User::firstOrCreate(
            ['login' => $row['login']],
            [
                'nom'       => $row['nom'],
                'prenom'    => $row['prenom'],
                'email'     => $row['email'],
                'telephone' => $row['telephone'] ?? null,
                'role'      => 'APPRENANT',
                'statut'    => 'actif',
                'password'  => Hash::make($row['login']), // mot de passe provisoire = login
                'fonction'  => 'etudiant',
            ]
        );

        // Éviter les doublons d'inscription
        $existe = Inscription::where([
            'etudiant_id'      => $etudiant->id,
            'classe_id'        => $this->classeId,
            'annee_scolaire_id' => $this->anneeScolaireId,
        ])->exists();

        if ($existe) {
            return null;
        }

        return new Inscription([
            'etudiant_id'       => $etudiant->id,
            'classe_id'         => $this->classeId,
            'annee_scolaire_id' => $this->anneeScolaireId,
        ]);
    }
}
