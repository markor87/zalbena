<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Upravljanje korisnicima je iza is.admin middleware-a.
 */
class AdminPristupTest extends TestCase
{
    use RefreshDatabase;

    public function test_obican_korisnik_ne_moze_do_liste_korisnika(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->getJson('/api/korisnici')->assertForbidden();
    }

    public function test_obican_korisnik_ne_moze_da_kreira_korisnika(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)->postJson('/api/korisnici', [
            'name' => 'Novi',
            'email' => 'novi@example.com',
            'password' => 'lozinka123',
            'role' => 'admin',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'novi@example.com']);
    }

    public function test_admin_moze_do_liste_korisnika(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->getJson('/api/korisnici')->assertOk();
    }

    public function test_neprijavljen_korisnik_dobija_401_a_ne_403(): void
    {
        $this->getJson('/api/korisnici')->assertUnauthorized();
    }
}
