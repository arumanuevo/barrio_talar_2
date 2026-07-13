<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ============================================
// RUTAS DE PRUEBA (SIN AUTENTICACIÓN)
// ============================================

Route::get('/test-simple', function() {
    return response()->json([
        'message' => '✅ Ruta API funcionando',
        'timestamp' => date('Y-m-d H:i:s')
    ]);
});

// ============================================
// RUTAS DE IMPORTACIÓN (SIN AUTENTICACIÓN PARA PRUEBAS)
// ============================================

// Ruta para probar el controlador (GET)
Route::get('/test-controller', [App\Http\Controllers\ImportMedicionesController::class, 'test']);

// Ruta para probar import con datos (POST)
Route::post('/test-import', [App\Http\Controllers\ImportMedicionesController::class, 'testImport']);

// Ruta PRINCIPAL de importación (POST)
Route::post('/import-mediciones/import', [App\Http\Controllers\ImportMedicionesController::class, 'import'])
    ->name('api.import.mediciones.import');

// ============================================
// RUTAS AUTENTICADAS
// ============================================

Route::middleware('auth:sanctum')->group(function () {
   
   
});

Route::middleware(['auth'])->group(function () {
   
});

Route::get('/getToken', [App\Http\Controllers\ApiGeneral::class, 'getToken'])->name('getToken');
Route::post('/postMed', [App\Http\Controllers\ApiGeneral::class, 'postMed'])->name('postMed');
Route::post('/postBorrarMedicion', [App\Http\Controllers\ApiGeneral::class, 'postBorrarMedicion'])->name('postBorrarMedicion');
Route::put('/actualizarMedicion/{id}', [App\Http\Controllers\GetTodasMed::class, 'actualizarMedicion'])->name('actualizarMedicion');
Route::put('/lotes/{id}', [App\Http\Controllers\LoteController::class, 'update'])->name('actualizarLote');
Route::get('/calcularDesdeHasta', [App\Http\Controllers\ApiGeneral::class, 'calcularDesdeHasta'])->name('calcularDesdeHasta');
Route::get('/getLotes', [App\Http\Controllers\ApiGeneral::class, 'getLotes'])->name('getLotes');
Route::get('/getGuardarFacturas', [App\Http\Controllers\ApiGeneral::class, 'getGuardarFacturas'])->name('getGuardarFacturas');
Route::post('/postGuardarFacturas', [App\Http\Controllers\ApiGeneral::class, 'postGuardarFacturas'])->name('postGuardarFacturas');

Route::get('/getMedidor', [App\Http\Controllers\ApiGeneral::class, 'getMedidor'])->name('getMedidor');


