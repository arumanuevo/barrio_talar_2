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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

//Route::get('/getMedidor/{lote}', [getCodMed::class, 'getCodMed'])->name('getCodMed');
Route::get('/getMedidor', [App\Http\Controllers\ApiGeneral::class, 'getMedidor'])->name('getMedidor');
Route::get('/getToken', [App\Http\Controllers\ApiGeneral::class, 'getToken'])->name('getToken');
Route::post('/postMed', [App\Http\Controllers\ApiGeneral::class, 'postMed'])->name('postMed');

//Route::get('/getTodasMed', [App\Http\Controllers\GetTodasMed::class, 'getTodasMed'])->name('getTodasMed');
Route::post('/postBorrarMedicion', [App\Http\Controllers\ApiGeneral::class, 'postBorrarMedicion'])->name('postBorrarMedicion');
Route::put('/actualizarMedicion/{id}', [App\Http\Controllers\GetTodasMed::class, 'actualizarMedicion'])->name('actualizarMedicion');
Route::put('/lotes/{id}', [App\Http\Controllers\LoteController::class, 'update'])->name('actualizarLote'); // Ruta para actualizar el lote y usuario
Route::get('/calcularDesdeHasta', [App\Http\Controllers\ApiGeneral::class, 'calcularDesdeHasta'])->name('calcularDesdeHasta');
Route::get('/getLotes', [App\Http\Controllers\ApiGeneral::class, 'getLotes'])->name('getLotes');
Route::get('/getGuardarFacturas', [App\Http\Controllers\ApiGeneral::class, 'getGuardarFacturas'])->name('getGuardarFacturas');
Route::post('/postGuardarFacturas', [App\Http\Controllers\ApiGeneral::class, 'postGuardarFacturas'])->name('postGuardarFacturas');

