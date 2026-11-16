<?php

namespace Tests\Unit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PHPUnit\Framework\TestCase;

/**
 * Helper za smer sortiranja. Laravel-ov orderBy prihvata samo asc/desc i na
 * sve ostalo baca izuzetak, pa je ovde poenta da neispravan ulaz nikada ne
 * stigne do upita.
 */
class SortDirectionTest extends TestCase
{
    private function pozovi(array $params, string $default = 'desc'): string
    {
        $controller = new class extends Controller {
            public function smer(Request $request, string $default): string
            {
                return $this->sortDirection($request, $default);
            }
        };

        return $controller->smer(new Request($params), $default);
    }

    public function test_propusta_asc_i_desc(): void
    {
        $this->assertSame('asc', $this->pozovi(['sort_direction' => 'asc']));
        $this->assertSame('desc', $this->pozovi(['sort_direction' => 'desc']));
    }

    public function test_ne_razlikuje_velicinu_slova(): void
    {
        $this->assertSame('asc', $this->pozovi(['sort_direction' => 'ASC']));
        $this->assertSame('desc', $this->pozovi(['sort_direction' => 'DeSc']));
    }

    public function test_vraca_podrazumevani_smer_za_neispravnu_vrednost(): void
    {
        $this->assertSame('desc', $this->pozovi(['sort_direction' => 'foo']));
        $this->assertSame('desc', $this->pozovi(['sort_direction' => '']));
        $this->assertSame('desc', $this->pozovi(['sort_direction' => 'asc; drop table zalbe']));
    }

    public function test_vraca_podrazumevani_smer_kada_parametra_nema(): void
    {
        $this->assertSame('desc', $this->pozovi([]));
        $this->assertSame('asc', $this->pozovi([], 'asc'));
    }

    public function test_ne_puca_na_nizu_ili_broju(): void
    {
        $this->assertSame('desc', $this->pozovi(['sort_direction' => 123]));
    }
}
