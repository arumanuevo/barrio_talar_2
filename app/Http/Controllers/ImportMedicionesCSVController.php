<?php
// app/Http/Controllers/ImportMedicionesCSVController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportMedicionesCSVController extends Controller
{
    /**
     * Muestra el formulario de importación de CSV
     */
    public function showImportForm()
    {
        return view('import-mediciones-csv');
    }

    /**
     * Analiza el archivo CSV y genera un informe de previsualización
     */
     /**
     * Versión de prueba - SOLO PARA DEPURAR
     */
    public function previewCSV(Request $request)
    {
        // ✅ Devolver siempre una respuesta JSON simple para verificar que el método se ejecuta
        return response()->json([
            'success' => true,
            'message' => 'El método previewCSV se ejecutó correctamente',
            'received_file' => $request->hasFile('file') ? $request->file('file')->getClientOriginalName() : 'No file'
        ]);
    }

    public function importCSV(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'El método importCSV se ejecutó correctamente'
        ]);
    }

    public function downloadReport(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'El método downloadReport se ejecutó correctamente'
        ]);
    }

    /**
     * Analiza los datos y genera un informe completo
     */
    private function analyzeData($data)
    {
        $report = [
            'total' => count($data),
            'new_measurements' => 0,
            'duplicates' => 0,
            'errors' => [],
            'warnings' => [],
            'valid_data' => [],
            'missing_users' => [],
            'medidor_mismatch' => []
        ];

        try {
            // Obtener todos los usuarios para validación
            $allUsers = User::whereNotNull('lote')->get()->keyBy('lote');

            // Obtener todas las últimas mediciones por lote
            $lastMeasurements = [];
            $allLotes = Medicion::select('lote', DB::raw('MAX(fecha) as ultima_fecha'))
                ->groupBy('lote')
                ->get();

            foreach ($allLotes as $loteInfo) {
                $last = Medicion::where('lote', $loteInfo->lote)
                    ->where('fecha', $loteInfo->ultima_fecha)
                    ->first();
                if ($last) {
                    $lastMeasurements[$loteInfo->lote] = $last;
                }
            }

            foreach ($data as $index => $row) {
                $rowNumber = $index + 2;

                // ✅ Obtener valores del CSV
                $lote = isset($row['lote']) ? trim($row['lote']) : '';
                $medidorCSV = isset($row['medidor']) ? trim($row['medidor']) : '';
                $valormedido = isset($row['valormedido']) ? trim($row['valormedido']) : '';
                $fechaStr = isset($row['fecha']) ? trim($row['fecha']) : '';
                $foto = isset($row['foto']) ? trim($row['foto']) : 'Sin foto';
                $inspector = isset($row['inspector']) ? trim($row['inspector']) : 'admin';
                $pagado = isset($row['pagado']) ? trim($row['pagado']) : 'NO';

                Log::debug("Fila $rowNumber: ", [
                    'lote' => $lote,
                    'medidor' => $medidorCSV,
                    'valormedido' => $valormedido,
                    'fecha' => $fechaStr
                ]);

                // ✅ Limpiar lote (eliminar ceros a la izquierda)
                $lote = $this->cleanLote($lote);

                // Validaciones básicas
                if (empty($lote)) {
                    $report['errors'][] = "Fila $rowNumber: Lote vacío";
                    continue;
                }

                if (empty($medidorCSV)) {
                    $report['errors'][] = "Fila $rowNumber: Medidor vacío";
                    continue;
                }

                // ✅ Limpiar valormedido (reemplazar coma por punto)
                if (is_string($valormedido)) {
                    $valormedido = str_replace(',', '.', $valormedido);
                }
                $valormedidoFloat = floatval($valormedido);

                if ($valormedidoFloat < 0) {
                    $report['errors'][] = "Fila $rowNumber: Valor negativo ($valormedidoFloat)";
                    continue;
                }

                if (empty($fechaStr)) {
                    $report['errors'][] = "Fila $rowNumber: Fecha vacía";
                    continue;
                }

                // ✅ Buscar usuario por LOTE
                $user = $allUsers->get($lote);
                
                if (!$user) {
                    $report['missing_users'][] = [
                        'row' => $rowNumber,
                        'lote' => $lote,
                        'medidor_csv' => $medidorCSV
                    ];
                    $report['errors'][] = "Fila $rowNumber: Lote $lote no encontrado en el sistema";
                    continue;
                }

                // ✅ Verificar medidor
                $medidorBD = $user->medidor;
                if ($medidorBD != $medidorCSV) {
                    $report['medidor_mismatch'][] = [
                        'row' => $rowNumber,
                        'lote' => $lote,
                        'medidor_csv' => $medidorCSV,
                        'medidor_bd' => $medidorBD
                    ];
                    $report['warnings'][] = "Fila $rowNumber: Medidor del CSV ($medidorCSV) no coincide con el registrado ($medidorBD). Se usará el de la BD.";
                    $medidor = $medidorBD;
                } else {
                    $medidor = $medidorCSV;
                }

                // ✅ Parsear fecha
                try {
                    $fecha = Carbon::parse($fechaStr)->startOfDay();
                } catch (\Exception $e) {
                    $report['errors'][] = "Fila $rowNumber: Fecha inválida ($fechaStr)";
                    continue;
                }

                // ✅ Verificar duplicado
                $exists = Medicion::where('lote', $lote)
                    ->where('fecha', $fecha)
                    ->exists();

                if ($exists) {
                    $report['duplicates']++;
                    $report['warnings'][] = "Fila $rowNumber: Ya existe medición para el lote $lote en fecha {$fecha->format('Y-m-d')}";
                    continue;
                }

                // ✅ Obtener la última medición del lote
                $lastMeasurement = $lastMeasurements[$lote] ?? null;

                // ✅ Calcular todos los valores
                if ($lastMeasurement) {
                    $indice = $lastMeasurement->indice + 1;
                    $medidaAnt = $lastMeasurement->valormedido;
                    $tomaAnt = $lastMeasurement->fecha;
                    
                    $consumoCalculado = $valormedidoFloat - $medidaAnt;
                    
                    if ($consumoCalculado < 0) {
                        $report['warnings'][] = "Fila $rowNumber: Consumo negativo: $consumoCalculado (Valor: $valormedidoFloat - Anterior: $medidaAnt)";
                    }
                } else {
                    $indice = 1;
                    $medidaAnt = 0;
                    $tomaAnt = null;
                    $consumoCalculado = 0;
                    
                    $report['warnings'][] = "Fila $rowNumber: No hay medición anterior para el lote $lote. Consumo = 0.";
                }

                // ✅ Calcular vencimiento
                $vencimiento = (clone $fecha)->addDays(30);

                // ✅ Guardar datos válidos
                $report['valid_data'][] = [
                    'row' => $rowNumber,
                    'lote' => $lote,
                    'medidor' => $medidor,
                    'medidor_csv' => $medidorCSV,
                    'fecha' => $fecha->format('Y-m-d'),
                    'vencimiento' => $vencimiento->format('Y-m-d'),
                    'tomaant' => $tomaAnt ? $tomaAnt->format('Y-m-d') : null,
                    'medidaant' => $medidaAnt,
                    'valormedido' => $valormedidoFloat,
                    'consumo' => $consumoCalculado,
                    'indice' => $indice,
                    'periodo' => 30,
                    'inspector' => $inspector,
                    'foto' => $foto,
                    'pagado' => $pagado
                ];

                $report['new_measurements']++;
            }

        } catch (\Exception $e) {
            Log::error('Error en analyzeData', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // Calcular estadísticas
        $report['summary'] = [
            'total_rows' => $report['total'],
            'valid_rows' => count($report['valid_data']),
            'errors_count' => count($report['errors']),
            'warnings_count' => count($report['warnings']),
            'duplicates_count' => $report['duplicates'],
            'medidor_mismatch_count' => count($report['medidor_mismatch'])
        ];

        return $report;
    }

    /**
     * Limpiar número de lote
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
     * Descarga el informe de la importación
     */
    
}