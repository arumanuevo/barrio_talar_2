<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\VerificarFotosController;
use App\Http\Controllers\MedidorController;
use App\Http\Controllers\ConsumoController;
use App\Http\Controllers\UsuarioParaLoginController;
use App\Http\Controllers\MedicionProvisoriaController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

// ============================================
// RUTAS DE PERFILES
// ============================================

Route::get('/ruta-admin', [App\Http\Controllers\Admin\AdminController::class, 'adminView']);
Route::get('/ruta-usuario', [App\Http\Controllers\User\UserController::class, 'userView']);
Route::get('/ruta-inspector', [App\Http\Controllers\Inspector\InspectorController::class, 'InspectorView']);
Route::get('/ruta-sinrol', [App\Http\Controllers\SinRol\SinRolController::class, 'sinRolView']);

// ============================================
// RUTAS GENERALES
// ============================================

Route::get('/ListaCompletaLotes', [App\Http\Controllers\ListaCompletaLotes::class, 'index'])->name('ListaCompletaLotes');
Route::get('/editarLoteMedidor/{id}', [App\Http\Controllers\LoteController::class, 'edit'])->name('editarLoteMedidor');
Route::get('/Medir', [App\Http\Controllers\Medir::class, 'Medir'])->name('Medir');
Route::get('/editarMedicion', [App\Http\Controllers\GetTodasMed::class, 'editarMedicion'])->name('editarMedicion');
Route::get('/getTodasMed', [App\Http\Controllers\GetTodasMed::class, 'getTodasMed'])->name('getTodasMed');
Route::get('/getTodasMedVista', [App\Http\Controllers\GetTodasMed::class, 'getTodasMedVista'])->name('getTodasMedVista');
Route::post('/guardar_imagenes', [App\Http\Controllers\ImagenController::class, 'guardarImagenes'])->name('guardar_imagenes');
Route::get('/mostrarFormularioCarga', [App\Http\Controllers\ImagenController::class, 'mostrarFormularioCarga'])->name('mostrarFormularioCarga');
Route::get('/generar-contrasenas', [PasswordController::class, 'index'])->name('generar-contrasenas');
Route::post('/generar-contrasenas', [PasswordController::class, 'generatePasswords']);
Route::get('/export-to-excel', [PasswordController::class, 'exportToExcel'])->name('export-to-excel');
Route::get('/ultimosConsumos', [App\Http\Controllers\UltimosConsumos::class, 'ultimosConsumos'])->name('ultimosconsumos');
Route::get('/calculoConsumos', [App\Http\Controllers\CalculoConsumos::class, 'index'])->name('index');
Route::get('/getTodasFacturas', [App\Http\Controllers\GetFacturas::class, 'getTodasFacturas'])->name('getTodasFacturas');
Route::get('/getFacturas', [App\Http\Controllers\GetFacturas::class, 'getFacturas'])->name('getFacturas');
Route::get('/getFacturasGrafVista', [App\Http\Controllers\GetFacturas::class, 'getFacturasGrafVista'])->name('getFacturasGrafVista');

// ============================================
// RUTAS DE AUTENTICACIÓN (PASSWORD RESET)
// ============================================

Route::get('password/reset', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
Route::post('password/email', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::get('password/reset/{token}', [ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('password/reset', [ResetPasswordController::class, 'reset'])->name('password.update');

// ============================================
// RUTAS DE MEDICIONES Y LOTES
// ============================================

Route::get('/vistaMedicionesLote', [App\Http\Controllers\VistaMedicionesLote::class, 'index'])->name('index');
Route::get('/exportar-mediciones', [App\Http\Controllers\GetTodasMed::class, 'exportarMediciones'])->name('exportarMediciones');
Route::get('/obtenerMedicionesFaltantes', [App\Http\Controllers\ListaCompletaLotes::class, 'getLotesFaltanMedir'])->name('obtenerMedicionesFaltantes');
Route::get('/lotes/data', [App\Http\Controllers\ListaCompletaLotes::class, 'getLotesData'])->name('lotes.data');

// ============================================
// RUTAS DE UTILIDADES
// ============================================

Route::get('/verificar-fotos', [VerificarFotosController::class, 'index'])->name('fotos.verificar');

Route::get('/clear-cache', function() {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('config:cache');
    Artisan::call('view:clear');
    Artisan::call('route:clear');
    return 'Caché limpiada!';
});

// ============================================
// RUTAS AUTENTICADAS
// ============================================

Route::middleware(['auth'])->group(function () {
    Route::get('/medidores', [MedidorController::class, 'index'])->name('medidores.index');
    Route::get('/medidores/create', [MedidorController::class, 'create'])->name('medidores.create');
    Route::post('/medidores', [MedidorController::class, 'store'])->name('medidores.store');
    Route::get('/medidores/{medidor}/edit', [MedidorController::class, 'edit'])->name('medidores.edit');
    Route::put('/medidores/{medidor}', [MedidorController::class, 'update'])->name('medidores.update');

    // Gráficos de consumo
    Route::get('/grafico-consumos', [ConsumoController::class, 'mostrarGraficoConsumos'])->name('grafico.consumos');
    Route::get('/datos-consumo', [ConsumoController::class, 'obtenerDatosConsumo'])->name('datos.consumo');
    Route::get('/detectar-anomalias', [ConsumoController::class, 'detectarAnomalias'])->name('detectar.anomalias');

    // Mediciones provisorias
    Route::get('/mediciones-provisorias', [MedicionProvisoriaController::class, 'index'])->name('mediciones_provisorias.index');
    Route::post('/mediciones-provisorias', [MedicionProvisoriaController::class, 'store'])->name('mediciones_provisorias.store');
    Route::get('/mediciones-provisorias/listado', [MedicionProvisoriaController::class, 'indexListado'])->name('mediciones_provisorias.listado');

    // Lotes faltantes
    Route::get('/lotes-faltantes', [ConsumoController::class, 'obtenerLotesFaltantes'])->name('lotes.faltantes');

    // Obtener medidor
    Route::get('/obtener-medidor/{lote}', [MedicionProvisoriaController::class, 'obtenerMedidor'])->name('obtener.medidor');

    // Importar usuarios
    Route::get('/importar-usuarios', [UsuarioParaLoginController::class, 'showUploadForm'])->name('usuarios_para_login.upload');
    Route::post('/importar-usuarios/procesar', [UsuarioParaLoginController::class, 'processCSV'])->name('usuarios_para_login.process_csv');
    Route::post('/importar-usuarios/vista-previa', [UsuarioParaLoginController::class, 'previewCSV'])->name('usuarios_para_login.preview_csv');
    Route::get('/migrar-usuarios', [UsuarioParaLoginController::class, 'migrateToUsers'])->name('usuarios_para_login.migrate_to_users');
});

// ============================================
// RUTAS DE FOTOS
// ============================================

Route::post('/subir-foto-medicion', 'App\Http\Controllers\ImagenController@subirFotoMedicion')
    ->name('subir_foto_medicion')
    ->middleware('auth');

// ============================================
// ✅ RUTA DE IMPORTACIÓN DE MEDICIONES (SIN AUTENTICACIÓN PARA PRUEBAS)
// ============================================

Route::get('/importar-mediciones', [App\Http\Controllers\ImportMedicionesController::class, 'showImportForm'])
    ->name('import.mediciones.form');


// routes/web.php
Route::get('/importar-mediciones-csv', [App\Http\Controllers\ImportMedicionesCSVController::class, 'showImportForm'])
    ->name('import.mediciones.csv.form')
    ->middleware('auth');

    


