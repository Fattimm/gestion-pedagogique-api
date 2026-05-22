<?php

namespace Database\Seeders;

use App\Models\Salle;
use Illuminate\Database\Seeder;

class SalleSeeder extends Seeder
{
    public function run(): void
    {
        $salles = [
            ['nom' => 'Amphi A',   'numero' => 'A01', 'nombre_places' => 100],
            ['nom' => 'Amphi B',   'numero' => 'B01', 'nombre_places' => 80],
            ['nom' => 'Salle 101', 'numero' => 'S101', 'nombre_places' => 40],
            ['nom' => 'Salle 102', 'numero' => 'S102', 'nombre_places' => 40],
            ['nom' => 'Labo Info', 'numero' => 'L01',  'nombre_places' => 30],
        ];

        foreach ($salles as $data) {
            Salle::firstOrCreate(['numero' => $data['numero']], $data);
        }
    }
}
