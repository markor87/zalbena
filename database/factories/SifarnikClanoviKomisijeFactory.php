<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SifarnikClanoviKomisije>
 */
class SifarnikClanoviKomisijeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ime' => fake()->firstName(),
            'prezime' => fake()->lastName(),
        ];
    }
}
