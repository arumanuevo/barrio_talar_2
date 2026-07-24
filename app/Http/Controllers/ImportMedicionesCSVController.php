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
    public function previewCSV(Request $request)
    {
        try {
            Log::info('=== INICIO previewCSV ===');
            
            $request->validate([
                'file' => 'required|file|mimes:csv,txt'
            ]);

            $file = $request->file('file');
            Log::info('Archivo recibido', [
                'name' => $file->getClientOriginalName(),
                'size' => $file->getSize()
            ]);

            // Leer el archivo CSV
            $handle = fopen($file->getPathname(), 'r');
            if (!$handle) {
                throw new \Exception('No se pudo abrir el archivo');
            }

            // Detectar delimitador (punto y coma o coma)
            $firstLine = fgets($handle);
            rewind($handle);
            $delimiter = strpos($firstLine, ';') !== false ? ';' : ',';
            Log::info('Delimitador detectado', ['delimiter' => $delimiter]);

            $lines = [];
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                $row = array_map(function($field) {
                    return trim($field, " \t\n\r\0\x0B\"");
                }, $row);
                $lines[] = $row;
            }
            fclose($handle);

            Log::info('Filas leídas', ['total' => count($lines)]);

            if (empty($lines) || count($lines) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo CSV está vacío o no tiene datos.'
                ], 400);
            }

            // Obtener encabezados
            $headers = array_map(function($h) {
                return strtolower(trim($h));
            }, $lines[0]);

            Log::info('Encabezados detectados', ['headers' => $headers]);

            // ✅ Mapear columnas del CSV a las de la tabla madre
            $columnMapping = $this->mapColumns($headers);
            Log::info('Mapeo de columnas', ['mapping' => $columnMapping]);

            // ✅ Verificar que las columnas mínimas requeridas existan
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
                    'message' => 'El archivo CSV no tiene las columnas requeridas: ' . implode(', ', $missingColumns) . 
                                 '. Columnas detectadas: ' . implode(', ', $headers)
                ], 400);
            }

            // Procesar datos
            $data = [];
            for ($i = 1; $i < count($lines); $i++) {
                if (empty($lines[$i]) || count($lines[$i]) < 2) continue;
                
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = isset($lines[$i][$index]) ? trim($lines[$i][$index]) : '';
                }
                $data[] = $row;
            }

            Log::info('Datos procesados', ['total_filas' => count($data)]);

            if (empty($data)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontraron datos válidos en el archivo CSV.'
                ], 400);
            }

            // Analizar los datos
            $report = $this->analyzeData($data);

            Log::info('Reporte generado', [
                'total' => $report['total'],
                'valid_data' => count($report['valid_data'])
            ]);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
            Log::error('Error en previewCSV', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al analizar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Mapea las columnas del CSV a los campos esperados
     */
    private function mapColumns($headers)
    {
        $mapping = [];

        foreach ($headers as $header) {
            $headerLower = strtolower($header);
            
            if (strpos($headerLower, 'lote') !== false || $headerLower === 'lote') {
                $mapping['lote'] = $header;
            } elseif (strpos($headerLower, 'medidor') !== false || $headerLower === 'medidor') {
                $mapping['medidor'] = $header;
            } elseif (strpos($headerLower, 'valormedido') !== false || 
                      strpos($headerLower, 'valor') !== false || 
                      $headerLower === 'consumo') {
                $mapping['valormedido'] = $header;
            } elseif (strpos($headerLower, 'fecha') !== false || $headerLower === 'fecha') {
                $mapping['fecha'] = $header;
            } elseif (strpos($headerLower, 'foto') !== false || $headerLower === 'foto') {
                $mapping['foto'] = $header;
            } elseif (strpos($headerLower, 'inspector') !== false || $headerLower === 'inspector') {
                $mapping['inspector'] = $header;
            } elseif (strpos($headerLower, 'pagado') !== false || $headerLower === 'pagado') {
                $mapping['pagado'] = $header;
            } elseif (strpos($headerLower, 'id') !== false || $headerLower === 'id') {
                $mapping['id'] = $header;
            }
        }

        // Valores por defecto si no se encontraron
        $mapping['lote'] = $mapping['lote'] ?? 'lote';
        $mapping['medidor'] = $mapping['medidor'] ?? 'medidor';
        $mapping['valormedido'] = $mapping['valormedido'] ?? 'valormedido';
        $mapping['fecha'] = $mapping['fecha'] ?? 'fecha';
        $mapping['foto'] = $mapping['foto'] ?? 'foto';
        $mapping['inspector'] = $mapping['inspector'] ?? 'inspector';
        $mapping['pagado'] = $mapping['pagado'] ?? 'pagado';

        return $mapping;
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

            // ✅ Obtener valores del CSV usando el mapeo
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

            // Limpiar valormedido (reemplazar coma por punto)
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
                $report['errors'][] = "Lote $lote: No encontrado en el sistema";
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
                // ✅ Usar el medidor de la BD (el correcto)
                $medidor = $medidorBD;
                $report['warnings'][] = "Fila $rowNumber: Medidor del CSV ($medidorCSV) no coincide con el registrado ($medidorBD). Se usará el de la BD.";
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

            // ✅ Verificar duplicado (misma fecha y lote)
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

            // ✅ Calcular todos los valores (siempre hay medición anterior porque ya existe la tabla madre)
            if ($lastMeasurement) {
                $indice = $lastMeasurement->indice + 1;
                $medidaAnt = $lastMeasurement->valormedido;
                $tomaAnt = $lastMeasurement->fecha;
                
                // ✅ El consumo es la diferencia con la medición anterior
                $consumoCalculado = $valormedidoFloat - $medidaAnt;
                
                if ($consumoCalculado < 0) {
                    $report['warnings'][] = "Fila $rowNumber: El valor medido ($valormedidoFloat) es menor que el último registrado ($medidaAnt). Consumo negativo: $consumoCalculado";
                }
            } else {
                // ✅ Si no hay medición anterior, es la primera (pero según tu lógica no debería pasar)
                $indice = 1;
                $medidaAnt = 0;
                $tomaAnt = null;
                $consumoCalculado = 0;
                
                $report['warnings'][] = "Fila $rowNumber: No hay medición anterior para el lote $lote. Se creará con consumo 0.";
            }

            // ✅ Calcular vencimiento (30 días después de la fecha)
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
                'pagado' => $pagado,
                'last_measurement' => $lastMeasurement
            ];

            $report['new_measurements']++;
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
     * Importa los datos válidos a la base de datos
     */
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
                    Log::debug("Importando: ", [
                        'lote' => $item['lote'],
                        'medidor' => $item['medidor'],
                        'valormedido' => $item['valormedido'],
                        'consumo' => $item['consumo'],
                        'fecha' => $item['fecha']
                    ]);

                    // Crear la medición directamente
                    Medicion::create([
                        'lote' => $item['lote'],
                        'medidor' => $item['medidor'],
                        'periodo' => $item['periodo'] ?? 30,
                        'indice' => $item['indice'],
                        'fecha' => $item['fecha'],
                        'vencimiento' => $item['vencimiento'],
                        'tomaant' => $item['tomaant'] ?? null,
                        'medidaant' => $item['medidaant'] ?? 0,
                        'valormedido' => $item['valormedido'],
                        'consumo' => $item['consumo'],
                        'inspector' => $item['inspector'] ?? auth()->user()->name ?? 'admin',
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

    /**
     * Descarga el informe de la importación
     */
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
            
            // Cabeceras de la tabla madre
            fputcsv($handle, ['=== INFORME DE IMPORTACIÓN CSV ===']);
            fputcsv($handle, ['']);
            
            // Cabeceras de columnas (todas las de la tabla madre)
            fputcsv($handle, [
                'Fila',
                'Lote',
                'Medidor (BD)',
                'Medidor (CSV)',
                'Fecha',
                'Vencimiento',
                'Toma Ant.',
                'Medida Ant.',
                'Valor Medido',
                'Consumo',
                'Índice',
                'Periodo',
                'Inspector',
                'Foto',
                'Pagado',
                'Estado'
            ]);

            // Datos válidos
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
                    $item['fecha'],
                    $item['vencimiento'],
                    $item['tomaant'] ?? 'NULL',
                    $item['medidaant'],
                    $item['valormedido'],
                    $item['consumo'],
                    $item['indice'],
                    $item['periodo'],
                    $item['inspector'],
                    $item['foto'],
                    $item['pagado'],
                    $estado
                ]);
            }

            // Errores
            if (!empty($reportData['errors'])) {
                fputcsv($handle, ['']);
                fputcsv($handle, ['=== ERRORES ===']);
                foreach ($reportData['errors'] as $error) {
                    fputcsv($handle, [$error]);
                }
            }

            // Advertencias
            if (!empty($reportData['warnings'])) {
                fputcsv($handle, ['']);
                fputcsv($handle, ['=== ADVERTENCIAS ===']);
                foreach ($reportData['warnings'] as $warning) {
                    fputcsv($handle, [$warning]);
                }
            }

            // Resumen
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