<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SifarnikStatusZalbe;

class SifarnikStatusZalbeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $statusi = SifarnikStatusZalbe::orderBy('status_zalbe', 'asc')->get();
        return response()->json($statusi);
    }
}
