<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Smer sortiranja iz zahteva.
     *
     * Laravel odbija sve osim asc/desc izuzetkom, pa bi neispravna vrednost
     * srusila upit greskom 500. Ovako se vraca na podrazumevani smer.
     */
    protected function sortDirection(Request $request, string $default = 'desc'): string
    {
        $direction = strtolower((string) $request->input('sort_direction', $default));

        return in_array($direction, ['asc', 'desc'], true) ? $direction : $default;
    }
}
