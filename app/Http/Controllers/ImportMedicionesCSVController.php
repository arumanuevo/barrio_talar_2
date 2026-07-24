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
    public function showImportForm()
    {
        return view('import-mediciones-csv');
    }

    public function previewCSV(Request $request)
    {
        try {
            Log::info('=== INICIO previewCSV ===');
            
            if (!$request->hasFile('file')) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se ha subido ningún archivo.'
                ], 400);
            }

            $file = $request->file('file');
            
            if (!$file->isValid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo no es válido.'
                ], 400);
            }

            Log::info('Archivo recibido', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);

            $handle = fopen($file->getPathname(), 'r');
            if (!$handle) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo abrir el archivo.'
                ], 400);
            }

            $firstLine = fgets($handle);
            rewind($handle);
            
            if ($firstLine === false) {
                fclose($handle);
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo está vacío.'
                ], 400);
            }
            
            $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
            Log::info('Delimitador detectado', ['delimiter' => $delimiter]);

            $lines = [];
            while (($row = fgetcsv($handle, 0, $delimiter, '"')) !== false) {
                $row = array_map(function($field) {
                    return trim($field, " \t\n\r\0\x0B\"");
                }, $row);
                if (count(array_filter($row)) > 0) {
                    $lines[] = $row;
                }
            }
            fclose($handle);

            Log::info('Filas leídas', ['total' => count($lines)]);

            if (empty($lines) || count($lines) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo CSV está vacío o no tiene datos.'
                ], 400);
            }

            $headers = array_map(function($h) {
                return strtolower(trim($h));
            }, $lines[0]);

            Log::info('Encabezados detectados', ['headers' => $headers]);

            $requiredColumns = ['lote', 'medidor', 'valormedido', 'fecha'];
            $missingColumns = [];
            foreach ($requiredColumns as $col) {
                if (!in_array($col, $headers)) {
                    $missingColumns[] = $col;
                }
            }

            if (!empty($missingColumns)) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo CSV no tiene las columnas requeridas: ' . implode(', ', $missingColumns)
                ], 400);
            }

            $data = [];
            for ($i = 1; $i < count($lines); $i++) {
                if (empty($lines[$i]) || count($lines[$i]) < 2) continue;
                
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = isset($lines[$i][$index]) ? trim($lines[$i][$index]) : '';
                }
                $data[] = $row;
            }

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron datos válidos en el archivo CSV.'
                ], 400);
            }

            $report = $this->analyzeData($data);

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $report['total'],
                    'new_measurements' => $report['new_measurements'],
                    'duplicates' => $report['duplicates'],
                    'errors' => $report['errors'],
                    'warnings' => $report['warnings'],
                    'valid_data' => $report['valid_data'],
                    'missing_users' => $report['missing_users'],
                    'medidor_mismatch' => $report['medidor_mismatch'],
                    'summary' => $report['summary']
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error en previewCSV', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al analizar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

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
            'medidor_mismatch' => [],
            'summary' => []
        ];

        $allUsers = User::whereNotNull('lote')->get()->keyBy('lote');

        // Obtener últimas mediciones
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

            $lote = isset($row['lote']) ? trim($row['lote']) : '';
            $medidorCSV = isset($row['medidor']) ? trim($row['medidor']) : '';
            $valormedido = isset($row['valormedido']) ? trim($row['valormedido']) : '';
            $fechaStr = isset($row['fecha']) ? trim($row['fecha']) : '';
            $foto = isset($row['foto']) ? trim($row['foto']) : 'Sin foto';

            $lote = $this->cleanLote($lote);

            if (empty($lote)) {
                $report['errors'][] = "Fila $rowNumber: Lote vacío";
                continue;
            }

            if (empty($medidorCSV)) {
                $report['errors'][] = "Fila $rowNumber: Medidor vacío";
                continue;
            }

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

            $lastMeasurement = $lastMeasurements[$lote] ?? null;

            // ✅ Calcular valores
            if ($lastMeasurement) {
                $indice = $lastMeasurement->indice + 1;
                $medidaAnt = $lastMeasurement->valormedido;
                $tomaAnt = $lastMeasurement->fecha;
                $consumoCalculado = $valormedidoFloat - $medidaAnt;
                
                if ($consumoCalculado < 0) {
                    $report['warnings'][] = "Fila $rowNumber: Consumo negativo: $consumoCalculado";
                }
            } else {
                $indice = 1;
                $medidaAnt = 0;
                $tomaAnt = null;
                $consumoCalculado = 0;
                $report['warnings'][] = "Fila $rowNumber: No hay medición anterior para el lote $lote. Consumo = 0.";
            }

            $vencimiento = (clone $fecha)->addDays(30);

            // ✅ Guardar datos válidos - TODOS LOS CAMPOS COMO STRINGS O NULL
            $report['valid_data'][] = [
                'row' => $rowNumber,
                'lote' => $lote,
                'medidor' => $medidor,
                'medidor_csv' => $medidorCSV,
                'seccion' => null,
                'periodo' => 30,
                'indice' => $indice,
                'fecha' => $fecha->format('Y-m-d'),
                'vencimiento' => $vencimiento->format('Y-m-d'),
                'tomaant' => $tomaAnt ? $tomaAnt->format('Y-m-d') : null,
                'medidaant' => $medidaAnt,
                'valormedido' => $valormedidoFloat,
                'consumo' => $consumoCalculado,
                'inspector' => 'admin',
                'foto' => $foto,
                'pagado' => 'NO'
            ];

            $report['new_measurements']++;
        }

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

    public function importCSV(Request $request)
    {
        try {
            Log::info('=== INICIO importCSV ===');
            
            $request->validate([
                'data' => 'required|array',
                'data.*.lote' => 'required|string',
                'data.*.medidor' => 'required|string',
                'data.*.valormedido' => 'required|numeric',
                'data.*.fecha' => 'required|date'
            ]);

            $importData = $request->data;
            Log::info('Datos a importar', ['total' => count($importData)]);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($importData as $index => $item) {
                try {
                    Medicion::create([
                        'lote' => $item['lote'],
                        'seccion' => $item['seccion'] ?? null,
                        'medidor' => $item['medidor'],
                        'periodo' => $item['periodo'] ?? 30,
                        'indice' => $item['indice'],
                        'fecha' => $item['fecha'],
                        'vencimiento' => $item['vencimiento'],
                        'tomaant' => $item['tomaant'] ?? null,
                        'medidaant' => $item['medidaant'] ?? 0,
                        'valormedido' => $item['valormedido'],
                        'consumo' => $item['consumo'],
                        'inspector' => $item['inspector'] ?? 'admin',
                        'foto' => $item['foto'] ?? 'Sin foto',
                        'pagado' => $item['pagado'] ?? 'NO'
                    ]);

                    $successCount++;

                } catch (\Exception $e) {
                    Log::error('Error en item', [
                        'index' => $index,
                        'error' => $e->getMessage()
                    ]);
                    $errors[] = "Item $index (Lote: " . ($item['lote'] ?? 'desconocido') . "): " . $e->getMessage();
                    $errorCount++;
                }
            }

            DB::commit();

            Log::info('Importación completada', [
                'success_count' => $successCount,
                'error_count' => $errorCount
            ]);

            return response()->json([
                'success' => true,
                'message' => "Importación completada: $successCount mediciones guardadas, $errorCount errores.",
                'success_count' => $successCount,
                'error_count' => $errorCount,
                'errors' => $errors
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en importCSV', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadReport(Request $request)
    {
        try {
            $reportData = json_decode($request->input('report_data'), true);
            
            if (!$reportData) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay datos de informe para descargar.'
                ], 400);
            }

            $filename = "informe_importacion_csv_" . date('Y-m-d_H-i-s') . '.csv';
            
            $handle = fopen('php://temp', 'r+');
            
            fputcsv($handle, ['=== INFORME DE IMPORTACIÓN CSV ===']);
            fputcsv($handle, ['']);
            
            fputcsv($handle, [
                'Fila', 'Lote', 'Medidor (BD)', 'Medidor (CSV)', 'Seccion',
                'Periodo', 'Indice', 'Fecha', 'Vencimiento',
                'Toma Ant.', 'Medida Ant.', 'Valor Medido', 'Consumo',
                'Inspector', 'Foto', 'Pagado', 'Estado'
            ]);

            foreach ($reportData['valid_data'] as $item) {
                $estado = 'OK';
                if ($item['consumo'] < 0) {
                    $estado = 'Consumo Negativo';
                }
                
                fputcsv($handle, [
                    $item['row'],
                    $item['lote'],
                    $item['medidor'],
                    $item['medidor_csv'],
                    $item['seccion'] ?? 'NULL',
                    $item['periodo'],
                    $item['indice'],
                    $item['fecha'],
                    $item['vencimiento'],
                    $item['tomaant'] ?? 'NULL',
                    $item['medidaant'],
                    $item['valormedido'],
                    $item['consumo'],
                    $item['inspector'],
                    $item['foto'],
                    $item['pagado'],
                    $estado
                ]);
            }

            if (!empty($reportData['errors'])) {
                fputcsv($handle, ['']);
                fputcsv($handle, ['=== ERRORES ===']);
                foreach ($reportData['errors'] as $error) {
                    fputcsv($handle, [$error]);
                }
            }

            if (!empty($reportData['warnings'])) {
                fputcsv($handle, ['']);
                fputcsv($handle, ['=== ADVERTENCIAS ===']);
                foreach ($reportData['warnings'] as $warning) {
                    fputcsv($handle, [$warning]);
                }
            }

            fputcsv($handle, ['']);
            fputcsv($handle, ['=== RESUMEN ===']);
            fputcsv($handle, ['Total de registros:', $reportData['summary']['total_rows']]);
            fputcsv($handle, ['Registros válidos:', $reportData['summary']['valid_rows']]);
            fputcsv($handle, ['Errores:', $reportData['summary']['errors_count']]);
            fputcsv($handle, ['Advertencias:', $reportData['summary']['warnings_count']]);
            fputcsv($handle, ['Duplicados:', $reportData['summary']['duplicates_count']]);
            fputcsv($handle, ['Discrepancias de medidor:', $reportData['summary']['medidor_mismatch_count']]);
            fputcsv($handle, ['Fecha de generación:', date('Y-m-d H:i:s')]);
            fputcsv($handle, ['Usuario:', auth()->user()->name ?? 'admin']);

            rewind($handle);
            $csvContent = stream_get_contents($handle);
            fclose($handle);

            return response()->streamDownload(function () use ($csvContent) {
                echo $csvContent;
            }, $filename, [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => "attachment; filename=\"{$filename}\""
            ]);

        } catch (\Exception $e) {
            Log::error('Error en downloadReport', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al generar el informe: ' . $e->getMessage()
            ], 500);
        }
    }
}