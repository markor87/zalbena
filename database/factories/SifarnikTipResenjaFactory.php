<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SifarnikTipResenja>
 */
class SifarnikTipResenjaFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tip_resenja' => fake()->unique()->words(3, true),
        ];
    }
}
