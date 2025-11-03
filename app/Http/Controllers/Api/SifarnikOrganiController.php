<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SifarnikOrgani;

class SifarnikOrganiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $organi = SifarnikOrgani::orderBy('organ', 'asc')->get();
        return response()->json($organi);
    }
}
