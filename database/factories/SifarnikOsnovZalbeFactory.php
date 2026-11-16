<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SifarnikOsnovZalbe>
 */
class SifarnikOsnovZalbeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'osnov_zalbe' => fake()->unique()->words(3, true),
        ];
    }
}
