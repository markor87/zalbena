<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PodnosiocZalbe>
 */
class PodnosiocZalbeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'ime_podnosioca_zalbe' => fake()->randomElement(['Марко', 'Ана', 'Петар', 'Јелена', 'Никола']),
            'prezime_podnosioca_zalbe' => fake()->randomElement(['Петровић', 'Јовановић', 'Николић', 'Марковић']),
            'jmbg_podnosioca_zalbe' => fake()->numerify('#############'),
            'institucija_podnosioca_zalbe' => fake()->randomElement([
                'Министарство правде',
                'Градска управа',
                'Пореска управа',
            ]),
            'napomena' => null,
        ];
    }
}
