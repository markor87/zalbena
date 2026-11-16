<?php

namespace Database\Factories;

use App\Models\PodnosiocZalbe;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zalba>
 */
class ZalbaFactory extends Factory
{
    public function definition(): array
    {
        $prijem = fake()->dateTimeBetween('-3 years', '-1 month');
        $resavanje = fake()->dateTimeBetween($prijem, 'now');

        return [
            'prijemni_broj' => fake()->unique()->numerify('###-##/####'),
            'datum_prijema_zalbe' => $prijem,
            'broj_resenja' => fake()->numerify('####/####'),
            'osnov_zalbe' => fake()->sentence(),
            'datum_resavanja_na_zk' => $resavanje,
            'datum_ekspedicije_ds_organu' => $resavanje,
            'podnosioci_zalbe' => PodnosiocZalbe::factory(),
            'institucija' => fake()->randomElement(['Министарство правде', 'Градска управа']),
            'status_zalbe' => fake()->randomElement(['Решен', 'Нерешен', 'Упућен на допуну']),
            'napomena' => null,
            'naknada' => fake()->randomFloat(2, 0, 50000),
        ];
    }

    /**
     * Zalba koja je resena - ima datume odlucivanja i ekspedicije.
     */
    public function resena(): static
    {
        return $this->state(fn () => ['status_zalbe' => 'Решен']);
    }

    /**
     * Nerresena zalba - ulazi u izvestaj o neresenim zalbama.
     */
    public function neresena(): static
    {
        return $this->state(fn () => [
            'status_zalbe' => 'Нерешен',
            'datum_resavanja_na_zk' => null,
            'datum_ekspedicije_ds_organu' => null,
        ]);
    }

    /**
     * Zalba koja je zavrsila u upravnom sporu pred Upravnim sudom.
     */
    public function uUpravnomSporu(): static
    {
        return $this->state(fn () => [
            'datum_prijema_tuzbe_od_us' => fake()->dateTimeBetween('-2 years', '-6 months'),
            'datum_ekspedicije_odgovora_zk' => fake()->dateTimeBetween('-6 months', '-3 months'),
            'datum_prijema_odluke_us' => fake()->dateTimeBetween('-3 months', 'now'),
            'datum_donosenja_odluke_us' => fake()->dateTimeBetween('-4 months', '-1 month'),
            'broj_odluke_us' => fake()->numerify('У-####/####'),
        ]);
    }
}
