<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = User::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'nom' => $this->faker->lastName(),
            'prenom' => $this->faker->firstName(),
            'adresse' => $this->faker->address(),
            'telephone' => $this->faker->unique()->phoneNumber(),
            'fonction' => $this->faker->jobTitle(),
            'email' => $this->faker->unique()->safeEmail(),
            'login' => $this->faker->unique()->userName(),
            'password' => bcrypt('P@ssword123!'), // Mot de passe par défaut
            'photo' => null,  // Peut-être un chemin vers une photo
            'statut' => 'actif',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
