<?php

namespace Tests\Feature;

use App\Models\PodnosiocZalbe;
use App\Models\User;
use App\Models\Zalba;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ZalbaApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['role' => 'admin']);
    }

    public function test_lista_je_paginirana_po_deset(): void
    {
        Zalba::factory()->count(12)->create();

        $response = $this->actingAs($this->user)->getJson('/api/zalbe')->assertOk();

        $this->assertCount(10, $response->json('data'));
        $this->assertSame(12, $response->json('total'));
    }

    public function test_lista_vraca_podnosioca_uz_zalbu(): void
    {
        $podnosilac = PodnosiocZalbe::factory()->create([
            'ime_podnosioca_zalbe' => 'Милан',
            'prezime_podnosioca_zalbe' => 'Радовановић',
        ]);
        Zalba::factory()->create(['podnosioci_zalbe' => $podnosilac->id]);

        $response = $this->actingAs($this->user)->getJson('/api/zalbe')->assertOk();

        $this->assertSame('Милан', $response->json('data.0.podnosilac.ime_podnosioca_zalbe'));
    }

    public function test_pretraga_nalazi_po_imenu_i_prezimenu_zajedno(): void
    {
        $podnosilac = PodnosiocZalbe::factory()->create([
            'ime_podnosioca_zalbe' => 'Милан',
            'prezime_podnosioca_zalbe' => 'Радовановић',
        ]);
        Zalba::factory()->create(['podnosioci_zalbe' => $podnosilac->id]);
        Zalba::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/zalbe?search='.urlencode('Милан Радовановић'))
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
    }

    public function test_pretraga_nalazi_po_prijemnom_broju(): void
    {
        Zalba::factory()->create(['prijemni_broj' => '123-45/2026']);
        Zalba::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/zalbe?search=123-45')
            ->assertOk();

        $this->assertSame(1, $response->json('total'));
        $this->assertSame('123-45/2026', $response->json('data.0.prijemni_broj'));
    }

    public function test_filter_po_podnosiocu(): void
    {
        $podnosilac = PodnosiocZalbe::factory()->create();
        Zalba::factory()->count(2)->create(['podnosioci_zalbe' => $podnosilac->id]);
        Zalba::factory()->count(3)->create();

        $response = $this->actingAs($this->user)
            ->getJson('/api/zalbe?podnosilac_id='.$podnosilac->id)
            ->assertOk();

        $this->assertSame(2, $response->json('total'));
    }

    public function test_kreiranje_zalbe(): void
    {
        $podnosilac = PodnosiocZalbe::factory()->create();

        $this->actingAs($this->user)->postJson('/api/zalbe', [
            'prijemni_broj' => '999-01/2026',
            'datum_prijema_zalbe' => '2026-01-15',
            'podnosioci_zalbe' => $podnosilac->id,
            'status_zalbe' => 'Нерешен',
            'naknada' => 1500.50,
        ])->assertCreated()->assertJsonPath('prijemni_broj', '999-01/2026');

        $this->assertDatabaseHas('zalbe', [
            'prijemni_broj' => '999-01/2026',
            'status_zalbe' => 'Нерешен',
        ]);
    }

    public function test_kreiranje_zahteva_prijemni_broj_i_datum_prijema(): void
    {
        $this->actingAs($this->user)
            ->postJson('/api/zalbe', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['prijemni_broj', 'datum_prijema_zalbe']);

        $this->assertDatabaseCount('zalbe', 0);
    }

    public function test_kreiranje_odbija_neispravan_datum(): void
    {
        $this->actingAs($this->user)->postJson('/api/zalbe', [
            'prijemni_broj' => '999-02/2026',
            'datum_prijema_zalbe' => 'nije datum',
        ])->assertStatus(422)->assertJsonValidationErrors('datum_prijema_zalbe');
    }

    public function test_izmena_zalbe(): void
    {
        $zalba = Zalba::factory()->create(['status_zalbe' => 'Нерешен']);

        $this->actingAs($this->user)
            ->putJson('/api/zalbe/'.$zalba->id, ['status_zalbe' => 'Решен'])
            ->assertOk()
            ->assertJsonPath('status_zalbe', 'Решен');

        $this->assertDatabaseHas('zalbe', ['id' => $zalba->id, 'status_zalbe' => 'Решен']);
    }

    public function test_brisanje_zalbe(): void
    {
        $zalba = Zalba::factory()->create();

        $this->actingAs($this->user)
            ->deleteJson('/api/zalbe/'.$zalba->id)
            ->assertOk();

        $this->assertDatabaseMissing('zalbe', ['id' => $zalba->id]);
    }

    public function test_nepostojeca_zalba_vraca_404(): void
    {
        $this->actingAs($this->user)->getJson('/api/zalbe/999999')->assertNotFound();
    }
}
