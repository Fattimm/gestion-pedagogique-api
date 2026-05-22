<?php

namespace Database\Seeders;

use App\Models\Cours;
use App\Models\User;
use App\Models\Classe;
use App\Models\Module;
use App\Models\Semestre;
use App\Models\SessionDeCours;
use App\Models\Inscription;
use Illuminate\Database\Seeder;

class CoursSeeder extends Seeder
{
    public function run(): void
    {
        $sem1 = Semestre::where('libelle', 'Semestre 1')->first();
        if (!$sem1) return;

        $prof1 = User::where('login', 'ifall')->first();
        $prof2 = User::where('login', 'asow')->first();

        $modAlgo   = Module::where('libelle', 'Algorithmique')->first();
        $modBDD    = Module::where('libelle', 'Base de données')->first();
        $modWeb    = Module::where('libelle', 'Développement Web')->first();
        $modML     = Module::where('libelle', 'Machine Learning')->first();

        $classBDAI1 = Classe::where('libelle', 'BDAI-1')->first();
        $classDEV1  = Classe::where('libelle', 'DEV-1')->first();

        $coursData = [
            [
                'semestre_id'          => $sem1->id,
                'module_id'            => $modAlgo->id,
                'professeur_id'        => $prof1->id,
                'quota_horaire_global' => 30,
                'statut'               => 'en_cours',
                'classes'              => [$classBDAI1->id, $classDEV1->id],
            ],
            [
                'semestre_id'          => $sem1->id,
                'module_id'            => $modBDD->id,
                'professeur_id'        => $prof2->id,
                'quota_horaire_global' => 24,
                'statut'               => 'en_cours',
                'classes'              => [$classBDAI1->id],
            ],
            [
                'semestre_id'          => $sem1->id,
                'module_id'            => $modWeb->id,
                'professeur_id'        => $prof1->id,
                'quota_horaire_global' => 40,
                'statut'               => 'planifie',
                'classes'              => [$classDEV1->id],
            ],
        ];

        foreach ($coursData as $data) {
            $classeIds = $data['classes'];
            unset($data['classes']);

            $cours = Cours::firstOrCreate(
                ['semestre_id' => $data['semestre_id'], 'module_id' => $data['module_id'], 'professeur_id' => $data['professeur_id']],
                $data
            );
            $cours->classes()->syncWithoutDetaching($classeIds);

            // Créer une session de test pour le premier cours
            if ($cours->module_id === $modAlgo->id) {
                SessionDeCours::firstOrCreate(
                    ['cours_id' => $cours->id, 'date' => '2026-05-26'],
                    [
                        'cours_id'    => $cours->id,
                        'salle_id'    => 1,
                        'date'        => '2026-05-26',
                        'heure_debut' => '08:00',
                        'heure_fin'   => '10:00',
                        'nbre_heure'  => 2,
                        'type'        => 'presentiel',
                        'statut'      => 'planifiee',
                    ]
                );
            }
        }

        // Inscrire les étudiants dans BDAI-1 pour l'année 2024-2025
        $anneeId   = $sem1->annee_scolaire_id;
        $etudiants = User::where('role', 'APPRENANT')->get();
        foreach ($etudiants as $etudiant) {
            Inscription::firstOrCreate([
                'etudiant_id'       => $etudiant->id,
                'classe_id'         => $classBDAI1->id,
                'annee_scolaire_id' => $anneeId,
            ]);
        }
    }
}
