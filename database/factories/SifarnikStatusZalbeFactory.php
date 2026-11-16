<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SifarnikStatusZalbe>
 */
class SifarnikStatusZalbeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'status_zalbe' => fake()->unique()->words(3, true),
        ];
    }
}
