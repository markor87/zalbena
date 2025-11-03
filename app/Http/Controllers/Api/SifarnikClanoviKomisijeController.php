<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SifarnikClanoviKomisije;

class SifarnikClanoviKomisijeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $clanovi = SifarnikClanoviKomisije::orderBy('ime', 'asc')->get();
        return response()->json($clanovi);
    }
}
