<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Offsite\OffsiteController;
use App\Http\Controllers\Offsite\KkaController;

// =========================================================================
// MODUL OFFSITE AUDIT
// =========================================================================
Route::middleware(['auth:sanctum'])->prefix('offsite')->group(function () {
    
    // 1. Endpoint Upload File DUMP (Khusus RA)
    Route::post('/upload', [OffsiteController::class, 'upload']);

    // 2. Endpoint KKA Findings (Otomatis Filter Berdasarkan Wewenang Cabang RA/Admin)
    Route::get('/kka', [KkaController::class, 'index']);
    Route::put('/kka/{id}/ra', [KkaController::class, 'updateRa']);
    Route::put('/kka/{id}/admin', [KkaController::class, 'updateAdmin']);
    
});