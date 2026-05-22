<?php

namespace Database\Seeders;

use App\Models\AnneeScolaire;
use Illuminate\Database\Seeder;

class AnneeScolaireSeeder extends Seeder
{
    public function run(): void
    {
        AnneeScolaire::firstOrCreate(
            ['libelle' => '2024-2025'],
            ['date_debut' => '2024-10-01', 'date_fin' => '2025-07-31', 'etat' => 'en_cours']
        );
    }
}
