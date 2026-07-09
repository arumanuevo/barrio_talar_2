<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\getCodMed;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
// routes/api.php - CORREGIDO


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ✅ TODAS las rutas API deben estar dentro de auth:sanctum
Route::middleware('auth:sanctum')->group(function () {
    // Rutas existentes
    Route::get('/getMedidor', [App\Http\Controllers\ApiGeneral::class, 'getMedidor'])->name('getMedidor');
    Route::get('/getToken', [App\Http\Controllers\ApiGeneral::class, 'getToken'])->name('getToken');
    Route::post('/postMed', [App\Http\Controllers\ApiGeneral::class, 'postMed'])->name('postMed');
    Route::post('/postBorrarMedicion', [App\Http\Controllers\ApiGeneral::class, 'postBorrarMedicion'])->name('postBorrarMedicion');
    Route::put('/actualizarMedicion/{id}', [App\Http\Controllers\GetTodasMed::class, 'actualizarMedicion'])->name('actualizarMedicion');
    Route::put('/lotes/{id}', [App\Http\Controllers\LoteController::class, 'update'])->name('actualizarLote');
    Route::get('/calcularDesdeHasta', [App\Http\Controllers\ApiGeneral::class, 'calcularDesdeHasta'])->name('calcularDesdeHasta');
    Route::get('/getLotes', [App\Http\Controllers\ApiGeneral::class, 'getLotes'])->name('getLotes');
    Route::get('/getGuardarFacturas', [App\Http\Controllers\ApiGeneral::class, 'getGuardarFacturas'])->name('getGuardarFacturas');
    Route::post('/postGuardarFacturas', [App\Http\Controllers\ApiGeneral::class, 'postGuardarFacturas'])->name('postGuardarFacturas');

    // ✅ IMPORTACIÓN DE MEDICIONES - DENTRO DE AUTH
    Route::prefix('import-mediciones')->group(function () {
        Route::post('/import', [App\Http\Controllers\ImportMedicionesController::class, 'import'])
            ->name('api.import.mediciones.import');
    });
});
