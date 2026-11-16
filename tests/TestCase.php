<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use RuntimeException;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        // Mora pre parent::setUp(): tu se pravi aplikacija i otvara veza sa
        // bazom, pa provera posle toga ne bi nista spasila.
        $this->proveriTestnuBazu();

        parent::setUp();

        // Aplikacija je SPA: axios uvek posalje Referer sa istog hosta, pa
        // Sanctum-ov EnsureFrontendRequestsAreStateful ukljuci sesiju za /api
        // rute. Testni klijent ne salje to zaglavlje, pa bi bez ovoga svaki
        // poziv koji dira sesiju pao na "Session store not set on request".
        $this->withHeader('Referer', config('app.url'));
    }

    /**
     * Zastita od brisanja prave baze.
     *
     * Testovi upisuju i brisu redove. Ako phpunit.xml ostane bez DB_DATABASE
     * override-a, ime baze se cita iz .env-a - dakle iz prave baze. Zato se
     * ime trazi iskljucivo iz okruzenja u kome su testovi pokrenuti i mora
     * izgledati kao testno. Razorne artisan komande su zasebno ogranicene,
     * u AppServiceProvider-u.
     */
    private function proveriTestnuBazu(): void
    {
        $database = $_SERVER['DB_DATABASE'] ?? $_ENV['DB_DATABASE'] ?? getenv('DB_DATABASE');

        if ($database === false || $database === null || $database === '') {
            throw new RuntimeException(
                'DB_DATABASE nije postavljen za testove. Ime baze bi se uzelo iz .env-a, '.
                'dakle iz produkcije. Dodaj DB_DATABASE u phpunit.xml.'
            );
        }

        if ($database !== ':memory:' && ! str_ends_with($database, '_test')) {
            throw new RuntimeException(
                "Testovi su usmereni na bazu '{$database}', koja ne izgleda kao testna. ".
                "Ocekuje se ime koje zavrsava na '_test' ili sqlite u memoriji. ".
                'Zaustavljeno pre bilo kakve izmene baze.'
            );
        }
    }
}
