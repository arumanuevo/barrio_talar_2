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
        return view('import-mediciones');
    }

    /**
     * Importa las mediciones validadas.
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'data' => 'required|array',
                'data.*.lote' => 'required|string',
                'data.*.medidor' => 'required|string',
                'data.*.mediciones' => 'required|array|min:1',
            ]);

            $importData = $request->data;
            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($importData as $item) {
                $lote = $item['lote'];
                $medidor = $item['medidor'];

                // Buscar usuario por lote
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

                // Obtener última medición
                $lastMedicion = Medicion::where('lote', $lote)
                                        ->orderBy('fecha', 'desc')
                                        ->first();

                $indice = $lastMedicion ? $lastMedicion->indice + 1 : 1;
                $medidaAnt = $lastMedicion ? $lastMedicion->valormedido : 0;
                $tomaAnt = $lastMedicion ? $lastMedicion->fecha : null;

                foreach ($item['mediciones'] as $med) {
                    $fechaStr = $med['fecha'] ?? null;
                    $valor = (float) $med['valor'];

                    if (!$fechaStr) {
                        $errors[] = "Lote $lote: Fecha inválida.";
                        $errorCount++;
                        continue;
                    }

                    try {
                        $fecha = Carbon::parse($fechaStr)->startOfDay();
                    } catch (\Exception $e) {
                        $errors[] = "Lote $lote: Formato de fecha inválido ($fechaStr).";
                        $errorCount++;
                        continue;
                    }

                    // Verificar duplicado
                    $exists = Medicion::where('lote', $lote)
                                        ->where('fecha', $fecha)
                                        ->exists();
                    if ($exists) {
                        $errors[] = "Lote $lote: Ya existe medición para $fechaStr. Se omite.";
                        $errorCount++;
                        continue;
                    }

                    $consumo = $valor - $medidaAnt;
                    if ($consumo < 0) {
                        $errors[] = "Lote $lote: Consumo negativo ($consumo) para $fechaStr.";
                        $errorCount++;
                        continue;
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
            Log::error('Error en import: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }
}