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
     */
    private function cleanLote($lote)
    {
        if (empty($lote)) {
            return '';
        }

        $lote = trim($lote);

        if (is_numeric($lote)) {
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
                // ✅ Obtener lote y nombre para mensajes de error más claros
                $loteOriginal = isset($item['lote']) ? trim($item['lote']) : 'DESCONOCIDO';
                $nombre = isset($item['nombre']) ? trim($item['nombre']) : 'Sin nombre';
                $medidor = isset($item['medidor']) ? trim($item['medidor']) : '';

                // ✅ Validar campos requeridos con mensajes específicos
                if (!isset($item['lote']) || empty(trim($item['lote']))) {
                    $errors[] = "Item $index (Lote: DESCONOCIDO, Nombre: $nombre): Falta el campo LOTE.";
                    $errorCount++;
                    continue;
                }

                if (!isset($item['medidor']) || empty(trim($item['medidor']))) {
                    $errors[] = "Item $index (Lote: $loteOriginal, Nombre: $nombre): Falta el campo MEDIDOR.";
                    $errorCount++;
                    continue;
                }

                if (!isset($item['mediciones']) || empty($item['mediciones'])) {
                    $errors[] = "Item $index (Lote: $loteOriginal, Nombre: $nombre): No tiene mediciones (columnas de consumo vacías).";
                    $errorCount++;
                    continue;
                }

                // ✅ Limpiar lote
                $lote = $this->cleanLote($loteOriginal);

                if (empty($lote)) {
                    $errors[] = "Item $index (Lote original: '$loteOriginal', Nombre: $nombre): Lote inválido después de limpiar.";
                    $errorCount++;
                    continue;
                }

                if (empty($medidor)) {
                    $errors[] = "Item $index (Lote: $lote, Nombre: $nombre): Medidor vacío.";
                    $errorCount++;
                    continue;
                }

                // Buscar usuario
                $user = User::where('lote', $lote)->first();
                
                if (!$user) {
                    $errors[] = "Lote $lote (original: '$loteOriginal', Nombre: $nombre): No encontrado en el sistema.";
                    $errorCount++;
                    continue;
                }

                if ($user->medidor != $medidor) {
                    $errors[] = "Lote $lote (Nombre: $nombre): Medidor $medidor no coincide con el registrado ({$user->medidor}).";
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
                    $fechaStr = isset($med['fecha']) ? $med['fecha'] : null;
                    $valor = isset($med['valor']) ? (float) $med['valor'] : null;

                    if (!$fechaStr) {
                        $errors[] = "Lote $lote (Nombre: $nombre), medición $medIdx: Fecha inválida o vacía.";
                        $errorCount++;
                        continue;
                    }

                    if ($valor === null) {
                        $errors[] = "Lote $lote (Nombre: $nombre), medición $medIdx: Valor inválido o vacío.";
                        $errorCount++;
                        continue;
                    }

                    try {
                        $fecha = Carbon::parse($fechaStr)->startOfDay();
                    } catch (\Exception $e) {
                        $errors[] = "Lote $lote (Nombre: $nombre), medición $medIdx: Formato de fecha inválido ($fechaStr).";
                        $errorCount++;
                        continue;
                    }

                    $exists = Medicion::where('lote', $lote)
                                        ->where('fecha', $fecha)
                                        ->exists();
                    if ($exists) {
                        $errors[] = "Lote $lote (Nombre: $nombre): Ya existe medición para {$fecha->format('Y-m-d')}. Se omite.";
                        $errorCount++;
                        continue;
                    }

                    // Cálculo de consumo: 0 para la primera, diferencia para las siguientes
                    if ($tomaAnt === null) {
                        $consumo = 0;
                    } else {
                        $consumo = $valor - $medidaAnt;
                        if ($consumo < 0) {
                            $errors[] = "Lote $lote (Nombre: $nombre): Consumo negativo ($consumo) para fecha {$fecha->format('Y-m-d')}.";
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