<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SifarnikTipPresude>
 */
class SifarnikTipPresudeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tip_presude' => fake()->unique()->words(3, true),
        ];
    }
}
