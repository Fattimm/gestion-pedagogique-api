<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            AnneeScolaireSeeder::class,
            ClasseSeeder::class,
            SalleSeeder::class,
            SemestreSeeder::class,
            ModuleSeeder::class,
            CoursSeeder::class,
        ]);
    }
}
