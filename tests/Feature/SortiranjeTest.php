<?php

namespace Tests\Feature;

use App\Models\SifarnikOsnovZalbe;
use App\Models\SifarnikTipPresude;
use App\Models\SifarnikTipResenja;
use App\Models\User;
use App\Models\Zalba;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Regresioni test za bele liste sortiranja.
 *
 * Bela lista u ZalbaController-u je jedno vreme sadrzala imena kolona koja ne
 * postoje u tabeli (datum_resavanja, broj_presude, datum_ekspedicije). Takav
 * sort_by je prosao proveru i pao na nivou baze. Ovde se za svaku dozvoljenu
 * vrednost zaista izvrsava upit, pa nepostojeca kolona odmah pada na testu.
 *
 * Isto vazi za sort_direction: svaka vrednost osim asc/desc mora da se svede
 * na podrazumevani smer, a ne da sruси upit.
 */
class SortiranjeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create(['role' => 'admin']);

        SifarnikOsnovZalbe::factory()->create();
        SifarnikTipResenja::factory()->create();
        SifarnikTipPresude::factory()->create();

        Zalba::factory()->count(3)->uUpravnomSporu()->create([
            'osnov_zalbe' => 1,
            'tipovi_resenja' => 1,
            'tipovi_presude_us' => 1,
        ]);
        Zalba::factory()->count(2)->neresena()->create([
            'osnov_zalbe' => 1,
            'tipovi_resenja' => 1,
        ]);
    }

    public static function zalbeKolone(): array
    {
        return array_map(
            fn ($k) => [$k],
            ['prijemni_broj', 'broj_resenja', 'datum_prijema_zalbe', 'status_zalbe', 'datum_unosa']
        );
    }

    #[DataProvider('zalbeKolone')]
    public function test_sve_dozvoljene_kolone_za_zalbe_rade(string $kolona): void
    {
        foreach (['asc', 'desc'] as $smer) {
            $this->actingAs($this->user)
                ->getJson("/api/zalbe?sort_by={$kolona}&sort_direction={$smer}")
                ->assertOk()
                ->assertJsonStructure(['data', 'current_page', 'total']);
        }
    }

    public static function izvestajiSaSortiranjem(): array
    {
        return [
            'po datumu prijema' => ['izvestaj-po-datumu-prijema', [
                'ime_i_prezime',
                'z.datum_prijema_zalbe',
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.broj_resenja',
                'z.status_zalbe',
            ]],
            'tuzbe od US' => ['izvestaj-tuzbe-od-us', [
                'ime_i_prezime',
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.datum_prijema_zalbe',
                'z.datum_prijema_tuzbe_od_us',
                'stp.tip_presude',
            ]],
            'datum ekspedicije' => ['izvestaj-datum-ekspedicije', [
                'ime_i_prezime',
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.broj_resenja',
                'z.datum_ekspedicije_ds_organu',
            ]],
            'ekspedovane tuzbe' => ['izvestaj-ekspedovane-tuzbe', [
                'ime_i_prezime',
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.datum_prijema_odluke_us',
                'z.datum_prijema_zalbe',
                'z.datum_prijema_tuzbe_od_us',
                'z.datum_ekspedicije_odgovora_zk',
                'z.status_zalbe',
            ]],
            'odluke suda' => ['izvestaj-odluke-suda', [
                'ime_i_prezime',
                'pz.institucija_podnosioca_zalbe',
                'z.prijemni_broj',
                'z.datum_prijema_odluke_us',
                'z.datum_prijema_zalbe',
                'z.datum_prijema_tuzbe_od_us',
                'z.status_zalbe',
                'stp.tip_presude',
            ]],
            'godisnji' => ['izvestaj-godisnji', [
                'pz.institucija_podnosioca_zalbe',
                'soz.osnov_zalbe',
                'str.tip_resenja',
                'z.datum_prijema_zalbe',
                'z.datum_resavanja_na_zk',
                'z.status_zalbe',
            ]],
        ];
    }

    #[DataProvider('izvestajiSaSortiranjem')]
    public function test_sve_dozvoljene_kolone_za_izvestaje_rade(string $ruta, array $kolone): void
    {
        foreach ($kolone as $kolona) {
            foreach (['asc', 'desc'] as $smer) {
                $this->actingAs($this->user)
                    ->getJson("/api/{$ruta}?sort_by={$kolona}&sort_direction={$smer}")
                    ->assertOk();
            }
        }
    }

    #[DataProvider('izvestajiSaSortiranjem')]
    public function test_neispravan_smer_sortiranja_ne_rusi_izvestaj(string $ruta): void
    {
        foreach (['', 'foo', 'ascending', "asc'; DROP TABLE zalbe; --"] as $smer) {
            $this->actingAs($this->user)
                ->getJson("/api/{$ruta}?sort_direction=".urlencode($smer))
                ->assertOk();
        }

        $this->assertDatabaseCount('zalbe', 5);
    }

    public function test_neispravan_smer_sortiranja_ne_rusi_listu_zalbi(): void
    {
        foreach (['', 'foo', 'ascending', "asc'; DROP TABLE zalbe; --"] as $smer) {
            $this->actingAs($this->user)
                ->getJson('/api/zalbe?sort_direction='.urlencode($smer))
                ->assertOk();
        }

        $this->assertDatabaseCount('zalbe', 5);
    }

    public function test_nepoznata_kolona_pada_na_podrazumevano_sortiranje(): void
    {
        $this->actingAs($this->user)
            ->getJson('/api/zalbe?sort_by=datum_resavanja')
            ->assertOk();

        $this->actingAs($this->user)
            ->getJson('/api/zalbe?sort_by='.urlencode('zalbe.id; DROP TABLE zalbe'))
            ->assertOk();

        $this->assertDatabaseCount('zalbe', 5);
    }

    public function test_sortiranje_zaista_menja_redosled(): void
    {
        $asc = $this->actingAs($this->user)
            ->getJson('/api/zalbe?sort_by=prijemni_broj&sort_direction=asc')
            ->json('data.*.prijemni_broj');

        $desc = $this->actingAs($this->user)
            ->getJson('/api/zalbe?sort_by=prijemni_broj&sort_direction=desc')
            ->json('data.*.prijemni_broj');

        $this->assertSame($asc, array_reverse($desc));
    }
}
