<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SifarnikTipPresude;

class SifarnikTipPresudeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tipovi = SifarnikTipPresude::orderBy('id', 'asc')->get();
        return response()->json($tipovi);
    }
}
