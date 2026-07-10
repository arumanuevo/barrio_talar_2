<?php
// app/Http/Controllers/ImportMedicionesController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportMedicionesController extends Controller
{
    /**
     * Muestra el formulario de importación.
     */
    public function showImportForm()
    {
        return view('import-mediciones');
    }

    /**
     * Importa las mediciones validadas desde Excel.
     */
    public function import(Request $request)
    {
        try {
            if (!$request->has('data')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron datos.'
                ], 400);
            }

            $importData = $request->data;
            
            if (!is_array($importData) || empty($importData)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El campo "data" debe ser un array no vacío.'
                ], 400);
            }

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($importData as $index => $item) {
                if (!isset($item['lote']) || !isset($item['medidor']) || !isset($item['mediciones'])) {
                    $errors[] = "Item $index: Faltan campos requeridos.";
                    $errorCount++;
                    continue;
                }

                $lote = trim($item['lote']);
                $medidor = trim($item['medidor']);

                if (empty($lote) || empty($medidor)) {
                    $errors[] = "Item $index: Lote o medidor vacío.";
                    $errorCount++;
                    continue;
                }

                $user = User::where('lote', $lote)->first();
                if (!$user) {
                    $errors[] = "Lote $lote: No encontrado en el sistema.";
                    $errorCount++;
                    continue;
                }

                if ($user->medidor != $medidor) {
                    $errors[] = "Lote $lote: Medidor $medidor no coincide con el registrado ({$user->medidor}).";
                    $errorCount++;
                    continue;
                }

                // Obtener última medición existente
                $lastMedicion = Medicion::where('lote', $lote)
                                        ->orderBy('fecha', 'desc')
                                        ->first();

                $indice = $lastMedicion ? $lastMedicion->indice + 1 : 1;
                $medidaAnt = $lastMedicion ? $lastMedicion->valormedido : 0;
                $tomaAnt = $lastMedicion ? $lastMedicion->fecha : null;

                // Procesar cada medición del lote (ordenadas por fecha)
                // Las mediciones ya vienen ordenadas desde el frontend
                foreach ($item['mediciones'] as $medIdx => $med) {
                    $fechaStr = $med['fecha'] ?? null;
                    $valor = isset($med['valor']) ? (float) $med['valor'] : null;

                    if (!$fechaStr) {
                        $errors[] = "Lote $lote, medición $medIdx: Fecha inválida.";
                        $errorCount++;
                        continue;
                    }

                    if ($valor === null || $valor < 0) {
                        $errors[] = "Lote $lote, medición $medIdx: Valor inválido ($valor).";
                        $errorCount++;
                        continue;
                    }

                    try {
                        $fecha = Carbon::parse($fechaStr)->startOfDay();
                    } catch (\Exception $e) {
                        $errors[] = "Lote $lote, medición $medIdx: Formato de fecha inválido ($fechaStr).";
                        $errorCount++;
                        continue;
                    }

                    // Verificar duplicado
                    $exists = Medicion::where('lote', $lote)
                                        ->where('fecha', $fecha)
                                        ->exists();
                    if ($exists) {
                        $errors[] = "Lote $lote: Ya existe medición para {$fecha->format('Y-m-d')}. Se omite.";
                        $errorCount++;
                        continue;
                    }

                    // ✅ Cálculo de consumo CORREGIDO
                    // Si es la primera medición del lote, el consumo es 0
                    if ($tomaAnt === null) {
                        $consumo = 0;
                    } else {
                        $consumo = $valor - $medidaAnt;
                        if ($consumo < 0) {
                            $errors[] = "Lote $lote: Consumo negativo ($consumo) para fecha {$fecha->format('Y-m-d')}.";
                            $errorCount++;
                            continue;
                        }
                    }

                    $vencimiento = (clone $fecha)->addDays(30);

                    Medicion::create([
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

                    $successCount++;
                    $indice++;
                    $medidaAnt = $valor;
                    $tomaAnt = $fecha;
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Importación completada: $successCount mediciones guardadas, $errorCount errores.",
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }
}