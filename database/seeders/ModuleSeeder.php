<?php

namespace Database\Seeders;

use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        $modules = [
            ['libelle' => 'Algorithmique',               'description' => 'Bases de l\'algorithmique et de la programmation'],
            ['libelle' => 'Base de données',             'description' => 'Conception et requêtage SQL'],
            ['libelle' => 'Développement Web',           'description' => 'HTML, CSS, JavaScript, Laravel'],
            ['libelle' => 'Machine Learning',            'description' => 'Apprentissage automatique supervisé et non supervisé'],
            ['libelle' => 'Réseaux & Protocoles',        'description' => 'TCP/IP, routage, commutation'],
            ['libelle' => 'Gestion de Projet',           'description' => 'Méthodes agiles, planification, suivi'],
            ['libelle' => 'Développement Mobile',        'description' => 'Flutter et React Native'],
        ];

        foreach ($modules as $data) {
            Module::firstOrCreate(['libelle' => $data['libelle']], $data);
        }
    }
}
