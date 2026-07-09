<?php
// app/Http/Controllers/ImportMedicionesController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportMedicionesController extends Controller
{
    /**
     * Muestra el formulario de importación.
     */
    public function showImportForm()
    {
        Log::info('=== showImportForm: Cargando vista de importación ===');
        return view('import-mediciones');
    }

    /**
     * Importa las mediciones validadas.
     */
    public function import(Request $request)
    {
        Log::info('=== INICIO import() ===');
        Log::info('Request method: ' . $request->method());
        Log::info('Request path: ' . $request->path());
        Log::info('Request all: ', $request->all());
        
        try {
            Log::info('Paso 1: Validando datos...');
            
            $validator = validator($request->all(), [
                'data' => 'required|array',
                'data.*.lote' => 'required|string',
                'data.*.medidor' => 'required|string',
                'data.*.mediciones' => 'required|array|min:1',
            ]);

            if ($validator->fails()) {
                Log::error('Validación fallida: ' . json_encode($validator->errors()->all()));
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación: ' . implode(', ', $validator->errors()->all())
                ], 422);
            }

            Log::info('Validación exitosa. Datos: ', $request->all());

            $importData = $request->data;
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            Log::info('Paso 2: Iniciando transacción DB...');
            DB::beginTransaction();

            foreach ($importData as $index => $item) {
                Log::info("Procesando item $index: ", $item);
                
                $lote = $item['lote'];
                $medidor = $item['medidor'];

                Log::info("Buscando usuario con lote: $lote");
                $user = User::where('lote', $lote)->first();
                
                if (!$user) {
                    $msg = "Lote $lote: No encontrado en el sistema.";
                    Log::warning($msg);
                    $errors[] = $msg;
                    $errorCount++;
                    continue;
                }

                Log::info("Usuario encontrado: ID {$user->id}, Medidor: {$user->medidor}");

                if ($user->medidor != $medidor) {
                    $msg = "Lote $lote: Medidor $medidor no coincide con el registrado ({$user->medidor}).";
                    Log::warning($msg);
                    $errors[] = $msg;
                    $errorCount++;
                    continue;
                }

                Log::info("Buscando última medición para lote: $lote");
                $lastMedicion = Medicion::where('lote', $lote)
                                        ->orderBy('fecha', 'desc')
                                        ->first();

                $indice = $lastMedicion ? $lastMedicion->indice + 1 : 1;
                $medidaAnt = $lastMedicion ? $lastMedicion->valormedido : 0;
                $tomaAnt = $lastMedicion ? $lastMedicion->fecha : null;

                Log::info("Última medición: indice=$indice, medidaAnt=$medidaAnt, tomaAnt=$tomaAnt");

                foreach ($item['mediciones'] as $medIdx => $med) {
                    Log::info("Procesando medición $medIdx: ", $med);
                    
                    $fechaStr = $med['fecha'] ?? null;
                    $valor = (float) $med['valor'];

                    if (!$fechaStr) {
                        $msg = "Lote $lote: Fecha inválida.";
                        Log::warning($msg);
                        $errors[] = $msg;
                        $errorCount++;
                        continue;
                    }

                    try {
                        $fecha = Carbon::parse($fechaStr)->startOfDay();
                        Log::info("Fecha parseada: " . $fecha->format('Y-m-d'));
                    } catch (\Exception $e) {
                        $msg = "Lote $lote: Formato de fecha inválido ($fechaStr). Error: " . $e->getMessage();
                        Log::warning($msg);
                        $errors[] = $msg;
                        $errorCount++;
                        continue;
                    }

                    Log::info("Verificando duplicado para lote $lote, fecha " . $fecha->format('Y-m-d'));
                    $exists = Medicion::where('lote', $lote)
                                        ->where('fecha', $fecha)
                                        ->exists();
                    if ($exists) {
                        $msg = "Lote $lote: Ya existe medición para {$fecha->format('Y-m-d')}. Se omite.";
                        Log::warning($msg);
                        $errors[] = $msg;
                        $errorCount++;
                        continue;
                    }

                    $consumo = $valor - $medidaAnt;
                    Log::info("Cálculo de consumo: $valor - $medidaAnt = $consumo");
                    
                    if ($consumo < 0) {
                        $msg = "Lote $lote: Consumo negativo ($consumo) para fecha {$fecha->format('Y-m-d')}.";
                        Log::warning($msg);
                        $errors[] = $msg;
                        $errorCount++;
                        continue;
                    }

                    $vencimiento = (clone $fecha)->addDays(30);
                    Log::info("Vencimiento calculado: " . $vencimiento->format('Y-m-d'));

                    Log::info("Creando medición para lote $lote...");
                    $medicion = Medicion::create([
                        'lote' => $lote,
                        'medidor' => $medidor,
                        'periodo' => 30,
                        'indice' => $indice,
                        'fecha' => $fecha,
                        'vencimiento' => $vencimiento,
                        'tomaant' => $tomaAnt,
                        'medidaant' => $medidaAnt,
                        'valormedido' => $valor,
                        'consumo' => $consumo,
                        'inspector' => $user->name ?? 'admin',
                        'foto' => 'Sin foto',
                        'pagado' => 'NO'
                    ]);

                    Log::info("Medición creada con ID: " . $medicion->id);

                    $successCount++;
                    $indice++;
                    $medidaAnt = $valor;
                    $tomaAnt = $fecha;
                }
            }

            Log::info("Paso 3: Confirmando transacción...");
            DB::commit();

            $response = [
                'success' => true,
                'message' => "Importación completada: $successCount mediciones guardadas, $errorCount errores.",
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ];

            Log::info('=== FIN import() EXITOSO ===', $response);
            return response()->json($response);

        } catch (\Exception $e) {
            Log::error('=== ERROR EN import() ===');
            Log::error('Mensaje: ' . $e->getMessage());
            Log::error('Archivo: ' . $e->getFile() . ':' . $e->getLine());
            Log::error('Trace: ' . $e->getTraceAsString());
            
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}