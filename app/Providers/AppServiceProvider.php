<?php

namespace App\Providers;

use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use RuntimeException;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Artisan komande koje brisu postojece tabele.
     *
     * Dozvoljene su isklucivo nad testnom bazom na ovoj masini. Prema bazi na
     * drugom serveru - a tu su i testni podaci na 10.15.32.77 i produkcija -
     * odbijaju se bez obzira na to ko ih je pozvao i iz kog okruzenja.
     */
    private const RAZORNE_KOMANDE = [
        'migrate:fresh',
        'migrate:refresh',
        'migrate:reset',
        'db:wipe',
    ];

    /**
     * Hostovi koji se smatraju ovom masinom.
     */
    private const LOKALNI_HOSTOVI = ['127.0.0.1', 'localhost', '::1', ''];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->ograniciRazorneKomande();
    }

    /**
     * Presretni razorne komande pre nego sto dodju do baze.
     *
     * CommandStarting se emituje pre izvrsavanja komande, pa izuzetak odavde
     * zaustavlja i poziv koji bi dosao iz test paketa.
     */
    private function ograniciRazorneKomande(): void
    {
        Event::listen(function (CommandStarting $event) {
            if (! in_array($event->command, self::RAZORNE_KOMANDE, true)) {
                return;
            }

            $veza = config('database.default');
            $host = (string) config("database.connections.{$veza}.host", '');
            $baza = (string) config("database.connections.{$veza}.database", '');

            if ($this->jeLokalnaTestnaBaza($host, $baza)) {
                return;
            }

            throw new RuntimeException(
                "Komanda '{$event->command}' brise postojece tabele i dozvoljena je samo ".
                "nad testnom bazom na ovoj masini. Trenutno je usmerena na host ".
                "'{$host}' i bazu '{$baza}'. Zaustavljeno pre bilo kakve izmene baze."
            );
        });
    }

    /**
     * Baza je testna samo ako je i na ovoj masini i ako joj ime to kaze.
     * Oba uslova moraju da vaze: ime '..._test' na udaljenom hostu nije dovoljno.
     */
    private function jeLokalnaTestnaBaza(string $host, string $baza): bool
    {
        if ($baza === ':memory:') {
            return true;
        }

        return in_array($host, self::LOKALNI_HOSTOVI, true)
            && str_ends_with($baza, '_test');
    }
}
