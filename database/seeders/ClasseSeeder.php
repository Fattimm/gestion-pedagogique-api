<?php

namespace Database\Seeders;

use App\Models\Classe;
use App\Models\AnneeScolaire;
use Illuminate\Database\Seeder;

class ClasseSeeder extends Seeder
{
    public function run(): void
    {
        $classes = [
            ['libelle' => 'BDAI-1', 'filiere' => 'Big Data & Intelligence Artificielle', 'niveau' => '1'],
            ['libelle' => 'BDAI-2', 'filiere' => 'Big Data & Intelligence Artificielle', 'niveau' => '2'],
            ['libelle' => 'DEV-1',  'filiere' => 'Développement Web & Mobile',           'niveau' => '1'],
            ['libelle' => 'DEV-2',  'filiere' => 'Développement Web & Mobile',           'niveau' => '2'],
            ['libelle' => 'RS-1',   'filiere' => 'Réseaux & Systèmes',                   'niveau' => '1'],
        ];

        $annee = AnneeScolaire::where('libelle', '2024-2025')->first();

        foreach ($classes as $data) {
            $classe = Classe::firstOrCreate(
                ['libelle' => $data['libelle']],
                $data
            );
            // Planifier la classe dans l'année en cours
            if ($annee) {
                $annee->classes()->syncWithoutDetaching([$classe->id]);
            }
        }
    }
}
