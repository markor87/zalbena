<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SifarnikOrgani>
 */
class SifarnikOrganiFactory extends Factory
{
    public function definition(): array
    {
        return [
            'organ' => fake()->unique()->words(3, true),
        ];
    }
}
