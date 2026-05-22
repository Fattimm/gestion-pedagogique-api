<?php

namespace Database\Seeders;

use App\Models\Semestre;
use App\Models\AnneeScolaire;
use Illuminate\Database\Seeder;

class SemestreSeeder extends Seeder
{
    public function run(): void
    {
        $annee = AnneeScolaire::where('libelle', '2024-2025')->first();
        if (!$annee) return;

        $semestres = [
            ['libelle' => 'Semestre 1', 'date_debut' => '2024-10-01', 'date_fin' => '2025-02-28'],
            ['libelle' => 'Semestre 2', 'date_debut' => '2025-03-01', 'date_fin' => '2025-07-31'],
        ];

        foreach ($semestres as $data) {
            Semestre::firstOrCreate(
                ['annee_scolaire_id' => $annee->id, 'libelle' => $data['libelle']],
                array_merge($data, ['annee_scolaire_id' => $annee->id])
            );
        }
    }
}
