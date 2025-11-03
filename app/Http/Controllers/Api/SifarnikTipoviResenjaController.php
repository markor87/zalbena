<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SifarnikTipResenja;

class SifarnikTipoviResenjaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipovi = SifarnikTipResenja::orderBy('id', 'asc')->get();
        return response()->json($tipovi);
    }
}
