<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SifarnikOsnovZalbe;

class SifarnikOsnovZalbeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $osnoviZalbe = SifarnikOsnovZalbe::orderBy('osnov_zalbe', 'asc')->get();
        return response()->json($osnoviZalbe);
    }
}
