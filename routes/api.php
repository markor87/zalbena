<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PodnosiocZalbeController;
use App\Http\Controllers\Api\ZalbaController;
use App\Http\Controllers\Api\IzvestajController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\SifarnikOrganiController;
use App\Http\Controllers\Api\SifarnikOsnovZalbeController;
use App\Http\Controllers\Api\SifarnikClanoviKomisijeController;
use App\Http\Controllers\Api\SifarnikTipoviResenjaController;
use App\Http\Controllers\Api\SifarnikStatusZalbeController;
use App\Http\Controllers\Api\SifarnikTipPresudeController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:web')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Podnosioci zalbe routes
    Route::get('podnosioci-zalbe/search', [PodnosiocZalbeController::class, 'search']);
    Route::apiResource('podnosioci-zalbe', PodnosiocZalbeController::class);

    // Zalbe routes
    Route::get('zalbe/export-pdf', [IzvestajController::class, 'exportZalbePdf']);
    Route::apiResource('zalbe', ZalbaController::class);

    // Sifarnik routes
    Route::get('sifarnik-organi', [SifarnikOrganiController::class, 'index']);
    Route::get('sifarnik-osnov-zalbe', [SifarnikOsnovZalbeController::class, 'index']);
    Route::get('sifarnik-clanovi-komisije', [SifarnikClanoviKomisijeController::class, 'index']);
    Route::get('sifarnik-tipovi-resenja', [SifarnikTipoviResenjaController::class, 'index']);
    Route::get('sifarnik-status-zalbe', [SifarnikStatusZalbeController::class, 'index']);
    Route::get('sifarnik-tip-presude', [SifarnikTipPresudeController::class, 'index']);

    // Izvestaji routes
    Route::get('izvestaj-neresene-zalbe', [IzvestajController::class, 'neresenZalbi']);
    Route::get('izvestaj-neresene-zalbe/export-excel', [IzvestajController::class, 'exportExcel']);
    Route::get('izvestaj-neresene-zalbe/export-pdf', [IzvestajController::class, 'exportPdf']);

    Route::get('izvestaj-po-datumu-prijema', [IzvestajController::class, 'poDatamuPrijema']);
    Route::get('izvestaj-po-datumu-prijema/export-excel', [IzvestajController::class, 'exportPoDatamuPrijemanExcel']);
    Route::get('izvestaj-po-datumu-prijema/export-pdf', [IzvestajController::class, 'exportPoDatamuPrijemaPdf']);

    Route::get('izvestaj-tuzbe-od-us', [IzvestajController::class, 'tuzbeOdUS']);
    Route::get('izvestaj-tuzbe-od-us/export-excel', [IzvestajController::class, 'exportTuzbeOdUSExcel']);
    Route::get('izvestaj-tuzbe-od-us/export-pdf', [IzvestajController::class, 'exportTuzbeOdUSPdf']);

    Route::get('izvestaj-datum-ekspedicije', [IzvestajController::class, 'datumEkspedicije']);
    Route::get('izvestaj-datum-ekspedicije/export-excel', [IzvestajController::class, 'exportDatumEkspedicijeExcel']);
    Route::get('izvestaj-datum-ekspedicije/export-pdf', [IzvestajController::class, 'exportDatumEkspedicijePdf']);

    Route::get('izvestaj-ekspedovane-tuzbe', [IzvestajController::class, 'ekspedovaneTuzbe']);
    Route::get('izvestaj-ekspedovane-tuzbe/export-excel', [IzvestajController::class, 'exportEkspedovaneTuzbeExcel']);
    Route::get('izvestaj-ekspedovane-tuzbe/export-pdf', [IzvestajController::class, 'exportEkspedovaneTuzbePdf']);

    Route::get('izvestaj-odluke-suda', [IzvestajController::class, 'odlukeSuda']);
    Route::get('izvestaj-odluke-suda/export-excel', [IzvestajController::class, 'exportOdlukeSudaExcel']);
    Route::get('izvestaj-odluke-suda/export-pdf', [IzvestajController::class, 'exportOdlukeSudaPdf']);

    Route::get('izvestaj-upravni-sporovi-u-toku', [IzvestajController::class, 'upravniSporoviUToku']);
    Route::get('izvestaj-upravni-sporovi-u-toku/export-excel', [IzvestajController::class, 'exportUpravniSporoviUTokuExcel']);
    Route::get('izvestaj-upravni-sporovi-u-toku/export-pdf', [IzvestajController::class, 'exportUpravniSporoviUTokuPdf']);

    Route::get('izvestaj-upravni-sporovi-po-godinama', [IzvestajController::class, 'upravniSporoviPoGodinama']);
    Route::get('izvestaj-upravni-sporovi-po-godinama/export-excel', [IzvestajController::class, 'exportUpravniSporoviPoGodinamaExcel']);
    Route::get('izvestaj-upravni-sporovi-po-godinama/export-pdf', [IzvestajController::class, 'exportUpravniSporoviPoGodinamaPdf']);

    // User management routes (admin only)
    Route::middleware('is.admin')->group(function () {
        Route::apiResource('korisnici', UserController::class);
    });
});
