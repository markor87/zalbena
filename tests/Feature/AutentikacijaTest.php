<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AutentikacijaTest extends TestCase
{
    use RefreshDatabase;

    public function test_prijava_sa_ispravnim_kredencijalima_vraca_korisnika(): void
    {
        $user = User::factory()->create([
            'email' => 'sluzbenik@example.com',
            'password' => bcrypt('tajna123'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'sluzbenik@example.com',
            'password' => 'tajna123',
        ]);

        $response->assertOk()->assertJsonPath('user.id', $user->id);
        $this->assertAuthenticatedAs($user);
    }

    public function test_prijava_sa_pogresnom_lozinkom_vraca_422(): void
    {
        User::factory()->create([
            'email' => 'sluzbenik@example.com',
            'password' => bcrypt('tajna123'),
        ]);

        $this->postJson('/api/login', [
            'email' => 'sluzbenik@example.com',
            'password' => 'pogresna',
        ])->assertStatus(422)->assertJsonValidationErrors('email');

        $this->assertGuest();
    }

    public function test_prijava_zahteva_email_i_lozinku(): void
    {
        $this->postJson('/api/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }

    public function test_neprijavljen_korisnik_ne_moze_do_podataka(): void
    {
        $this->getJson('/api/user')->assertUnauthorized();
        $this->getJson('/api/zalbe')->assertUnauthorized();
        $this->getJson('/api/izvestaj-godisnji')->assertUnauthorized();
    }

    public function test_odjava_prekida_sesiju(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->postJson('/api/logout')->assertOk();

        $this->assertGuest();
    }
}
