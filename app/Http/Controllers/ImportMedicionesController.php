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
     * ✅ LIMPIA EL NÚMERO DE LOTE:
     * - Elimina ceros a la izquierda (ej: 029 → 29)
     * - Elimina sufijos como -INQ (ya limpiado en Excel)
     * - Convierte a entero y luego a string para eliminar ceros a la izquierda
     */
    private function cleanLote($lote)
    {
        // Si está vacío, retornar vacío
        if (empty($lote)) {
            return '';
        }

        // Eliminar espacios
        $lote = trim($lote);

        // Si es un número (incluyendo con ceros a la izquierda), convertir a entero y luego a string
        if (is_numeric($lote)) {
            // Convertir a entero (elimina ceros a la izquierda automáticamente)
            $lote = (string) intval($lote);
        }

        return $lote;
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

                // ✅ LIMPIAR LOTE: eliminar ceros a la izquierda
                $loteOriginal = trim($item['lote']);
                $lote = $this->cleanLote($loteOriginal);
                $medidor = trim($item['medidor']);

                if (empty($lote) || empty($medidor)) {
                    $errors[] = "Item $index: Lote o medidor vacío (original: '$loteOriginal').";
                    $errorCount++;
                    continue;
                }

                // Buscar usuario con el lote limpio
                $user = User::where('lote', $lote)->first();
                
                if (!$user) {
                    $errors[] = "Lote $lote (original: '$loteOriginal'): No encontrado en el sistema.";
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

                foreach ($item['mediciones'] as $medIdx => $med) {
                    $fechaStr = $med['fecha'] ?? null;
                    $valor = isset($med['valor']) ? (float) $med['valor'] : null;

                    if (!$fechaStr || $valor === null) {
                        $errors[] = "Lote $lote, medición $medIdx: Datos inválidos.";
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

                    $exists = Medicion::where('lote', $lote)
                                        ->where('fecha', $fecha)
                                        ->exists();
                    if ($exists) {
                        $errors[] = "Lote $lote: Ya existe medición para {$fecha->format('Y-m-d')}. Se omite.";
                        $errorCount++;
                        continue;
                    }

                    // ✅ Cálculo de consumo: 0 para la primera, diferencia para las siguientes
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