<?php

namespace Tests\Feature;

use App\Models\PodnosiocZalbe;
use App\Models\SifarnikOsnovZalbe;
use App\Models\SifarnikTipPresude;
use App\Models\SifarnikTipResenja;
use App\Models\User;
use App\Models\Zalba;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Izvestaji su najosetljiviji deo aplikacije - svaki gradi sopstveni upit sa
 * vise leftJoin-ova i filtera. Ovde se proverava da svaki od njih prolazi i da
 * filtri po statusu zaista odvajaju zalbe.
 */
class IzvestajApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);

        SifarnikOsnovZalbe::factory()->create();
        SifarnikTipResenja::factory()->create();
        SifarnikTipPresude::factory()->create();
    }

    public static function sveRuteIzvestaja(): array
    {
        return array_map(fn ($r) => [$r], [
            'izvestaj-neresene-zalbe',
            'izvestaj-po-datumu-prijema',
            'izvestaj-tuzbe-od-us',
            'izvestaj-datum-ekspedicije',
            'izvestaj-ekspedovane-tuzbe',
            'izvestaj-odluke-suda',
            'izvestaj-upravni-sporovi-u-toku',
            'izvestaj-upravni-sporovi-po-godinama',
            'izvestaj-godisnji',
        ]);
    }

    #[DataProvider('sveRuteIzvestaja')]
    public function test_izvestaj_prolazi_na_praznoj_bazi(string $ruta): void
    {
        $this->actingAs($this->user)->getJson('/api/'.$ruta)->assertOk();
    }

    #[DataProvider('sveRuteIzvestaja')]
    public function test_izvestaj_prolazi_sa_podacima(string $ruta): void
    {
        $this->napuniPodatke();

        $this->actingAs($this->user)->getJson('/api/'.$ruta)->assertOk();
    }

    #[DataProvider('sveRuteIzvestaja')]
    public function test_izvestaj_prolazi_sa_pretragom(string $ruta): void
    {
        $this->napuniPodatke();

        $this->actingAs($this->user)
            ->getJson('/api/'.$ruta.'?search='.urlencode('Милан'))
            ->assertOk();
    }

    public function test_neresene_zalbe_prikazuju_samo_neresene(): void
    {
        $this->napuniPodatke();

        $rezultat = $this->actingAs($this->user)
            ->getJson('/api/izvestaj-neresene-zalbe')
            ->assertOk()
            ->json();

        $this->assertNotEmpty($rezultat);

        foreach ($rezultat as $red) {
            $this->assertContains($red['status_zalbe'], ['Нерешен', 'Упућен на допуну']);
        }
    }

    public function test_godisnji_izvestaj_filtrira_opseg_datuma_resavanja(): void
    {
        $podnosilac = PodnosiocZalbe::factory()->create();

        foreach (['2024-05-10', '2025-05-10'] as $datum) {
            Zalba::factory()->create([
                'podnosioci_zalbe' => $podnosilac->id,
                'datum_resavanja_na_zk' => $datum,
            ]);
        }

        $filteri = json_encode([[
            'field' => 'datum_resavanja_na_zk',
            'operator' => 'between',
            'value' => '2024-01-01',
            'value2' => '2024-12-31',
        ]]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/izvestaj-godisnji?advanced_filters='.urlencode($filteri))
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertSame('2024-05-10', $response->json('data.0.datum_resavanja_na_zk'));
    }

    public function test_izvoz_u_pdf_vraca_pdf(): void
    {
        $this->napuniPodatke();

        $response = $this->actingAs($this->user)
            ->get('/api/izvestaj-neresene-zalbe/export-pdf')
            ->assertOk();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }

    public function test_izvoz_u_excel_vraca_fajl(): void
    {
        $this->napuniPodatke();

        $this->actingAs($this->user)
            ->get('/api/izvestaj-neresene-zalbe/export-excel')
            ->assertOk();
    }

    private function napuniPodatke(): void
    {
        $podnosilac = PodnosiocZalbe::factory()->create([
            'ime_podnosioca_zalbe' => 'Милан',
            'prezime_podnosioca_zalbe' => 'Радовановић',
            'institucija_podnosioca_zalbe' => 'Министарство правде',
        ]);

        Zalba::factory()->count(2)->neresena()->create([
            'podnosioci_zalbe' => $podnosilac->id,
            'osnov_zalbe' => 1,
            'tipovi_resenja' => 1,
        ]);

        Zalba::factory()->count(2)->resena()->uUpravnomSporu()->create([
            'podnosioci_zalbe' => $podnosilac->id,
            'osnov_zalbe' => 1,
            'tipovi_resenja' => 1,
            'tipovi_presude_us' => 1,
        ]);

        Zalba::factory()->create([
            'podnosioci_zalbe' => $podnosilac->id,
            'osnov_zalbe' => 1,
            'tipovi_resenja' => 1,
            'status_zalbe' => 'Упућен на допуну',
            'datum_resavanja_na_zk' => null,
        ]);
    }
}
