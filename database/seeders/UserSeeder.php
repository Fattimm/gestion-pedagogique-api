<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'nom' => 'Diallo', 'prenom' => 'Mamadou', 'login' => 'mdiallo',
                'email' => 'mdiallo@ecole221.sn', 'telephone' => '771000001',
                'role' => 'MANAGER', 'fonction' => 'Responsable Pédagogique',
                'statut' => 'actif', 'password' => Hash::make('pedagogie@'),
            ],
            [
                'nom' => 'Ndiaye', 'prenom' => 'Fatou', 'login' => 'fndiaye',
                'email' => 'fndiaye@ecole221.sn', 'telephone' => '771000002',
                'role' => 'CM', 'fonction' => 'Attaché de cours',
                'statut' => 'actif', 'password' => Hash::make('pedagogie@'),
            ],
            [
                'nom' => 'Fall', 'prenom' => 'Ibrahima', 'login' => 'ifall',
                'email' => 'ifall@ecole221.sn', 'telephone' => '771000003',
                'role' => 'COACH', 'fonction' => 'Professeur',
                'statut' => 'actif', 'password' => Hash::make('pedagogie@'),
            ],
            [
                'nom' => 'Sow', 'prenom' => 'Aissatou', 'login' => 'asow',
                'email' => 'asow@ecole221.sn', 'telephone' => '771000004',
                'role' => 'COACH', 'fonction' => 'Professeur',
                'statut' => 'actif', 'password' => Hash::make('pedagogie@'),
            ],
            [
                'nom' => 'Sarr', 'prenom' => 'Cheikh', 'login' => 'csarr',
                'email' => 'csarr@ecole221.sn', 'telephone' => '771000005',
                'role' => 'APPRENANT', 'fonction' => 'etudiant',
                'statut' => 'actif', 'password' => Hash::make('pedagogie@'),
            ],
            [
                'nom' => 'Ba', 'prenom' => 'Mariama', 'login' => 'mba',
                'email' => 'mba@ecole221.sn', 'telephone' => '771000006',
                'role' => 'APPRENANT', 'fonction' => 'etudiant',
                'statut' => 'actif', 'password' => Hash::make('pedagogie@'),
            ],
            [
                'nom' => 'Diop', 'prenom' => 'Ousmane', 'login' => 'odiop',
                'email' => 'odiop@ecole221.sn', 'telephone' => '771000007',
                'role' => 'APPRENANT', 'fonction' => 'etudiant',
                'statut' => 'actif', 'password' => Hash::make('pedagogie@'),
            ],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(['login' => $data['login']], $data);
        }
    }
}
