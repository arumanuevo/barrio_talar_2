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

            // Detectar delimitador
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

            // Mapear columnas del CSV a los campos esperados
            $columnMapping = $this->mapColumns($headers);
            Log::info('Mapeo de columnas', ['mapping' => $columnMapping]);

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
            $report = $this->analyzeData($data, $columnMapping);

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
        $mapping = [
            'lote' => null,
            'medidor' => null,
            'consumo' => null,
            'fecha' => null,
            'foto' => null,
            'id' => null
        ];

        // Palabras clave para cada campo
        $keywords = [
            'lote' => ['lote', 'lotes', 'numero', 'nro'],
            'medidor' => ['medidor', 'medidores', 'codigo', 'cod'],
            'consumo' => ['consumo', 'valor', 'valormedido', 'medicion', 'medida'],
            'fecha' => ['fecha_medicion', 'fecha', 'fechamedicion', 'fechamedicion', 'date'],
            'foto' => ['foto', 'fotos', 'imagen', 'image', 'path', 'url'],
            'id' => ['id', 'numero', 'nro']
        ];

        foreach ($headers as $header) {
            $headerLower = strtolower($header);
            
            foreach ($keywords as $field => $words) {
                foreach ($words as $word) {
                    if (strpos($headerLower, $word) !== false) {
                        $mapping[$field] = $header;
                        break 2;
                    }
                }
            }
        }

        return $mapping;
    }

    /**
     * Analiza los datos y genera un informe completo
     */
    private function analyzeData($data, $columnMapping)
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
            'column_mapping' => $columnMapping
        ];

        // Obtener todos los lotes y medidores de la tabla madre
        $allUsers = User::whereNotNull('lote')->get()->keyBy('lote');

        foreach ($data as $index => $row) {
            $rowNumber = $index + 2; // +2 por encabezado y offset

            // Obtener valores usando el mapeo
            $lote = $this->getValueFromRow($row, $columnMapping['lote']);
            $medidorCSV = $this->getValueFromRow($row, $columnMapping['medidor']);
            $consumo = $this->getValueFromRow($row, $columnMapping['consumo']);
            $fechaMedicion = $this->getValueFromRow($row, $columnMapping['fecha']);
            $foto = $this->getValueFromRow($row, $columnMapping['foto']);

            // Limpiar lote (eliminar ceros a la izquierda)
            $lote = $this->cleanLote($lote);

            // Validaciones básicas
            if (empty($lote)) {
                $report['errors'][] = "Fila $rowNumber: Lote vacío";
                continue;
            }

            if (empty($consumo)) {
                $report['errors'][] = "Fila $rowNumber: Valor de consumo vacío";
                continue;
            }

            // Limpiar consumo (reemplazar coma por punto)
            if (is_string($consumo)) {
                $consumo = str_replace(',', '.', $consumo);
            }
            $consumoFloat = floatval($consumo);

            if ($consumoFloat < 0) {
                $report['errors'][] = "Fila $rowNumber: Consumo negativo ($consumoFloat)";
                continue;
            }

            if (empty($fechaMedicion)) {
                $report['errors'][] = "Fila $rowNumber: Fecha de medición vacía";
                continue;
            }

            // Buscar usuario por LOTE (ignorando medidor del CSV)
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

            // ✅ Verificar si el medidor del CSV coincide con el de la BD
            $medidorBD = $user->medidor;
            if ($medidorCSV && $medidorBD != $medidorCSV) {
                $report['medidor_mismatch'][] = [
                    'row' => $rowNumber,
                    'lote' => $lote,
                    'medidor_csv' => $medidorCSV,
                    'medidor_bd' => $medidorBD
                ];
                // ✅ No es un error crítico, solo una advertencia
                $report['warnings'][] = "Fila $rowNumber: Medidor del CSV ($medidorCSV) no coincide con el registrado ($medidorBD) para el lote $lote. Se usará el de la BD.";
            }

            // Verificar fecha
            try {
                $fecha = Carbon::parse($fechaMedicion)->startOfDay();
            } catch (\Exception $e) {
                $report['errors'][] = "Fila $rowNumber: Fecha inválida ($fechaMedicion)";
                continue;
            }

            // Obtener la última medición del lote desde la tabla madre
            $lastMeasurement = Medicion::where('lote', $lote)
                ->orderBy('fecha', 'desc')
                ->first();

            // Verificar duplicado exacto (misma fecha)
            $exists = Medicion::where('lote', $lote)
                ->where('fecha', $fecha)
                ->exists();

            if ($exists) {
                $report['duplicates']++;
                $report['warnings'][] = "Fila $rowNumber: Ya existe medición para el lote $lote en fecha $fechaMedicion";
                continue;
            }

            // ✅ Calcular el nuevo valormedido
            // El consumo en el CSV es el valor medido actual
            // El consumo en la tabla madre es la diferencia con la medición anterior
            $valormedido = $consumoFloat;
            $consumoCalculado = 0;

            if ($lastMeasurement) {
                $lastValor = $lastMeasurement->valormedido;
                $consumoCalculado = $valormedido - $lastValor;
                
                // Si el consumo calculado es negativo, es una advertencia
                if ($consumoCalculado < 0) {
                    $report['warnings'][] = "Fila $rowNumber: El valor medido ($valormedido) es menor que el último registrado ($lastValor) para el lote $lote. Consumo negativo: $consumoCalculado";
                }
            } else {
                // Primera medición del lote
                $consumoCalculado = 0;
                $report['warnings'][] = "Fila $rowNumber: Primera medición del lote $lote. Se creará con consumo 0.";
            }

            // Guardar datos válidos
            $report['valid_data'][] = [
                'lote' => $lote,
                'medidor' => $medidorBD, // ✅ Usar el medidor de la BD (el correcto)
                'valormedido' => $valormedido,
                'consumo' => $consumoCalculado,
                'fecha' => $fecha->format('Y-m-d'),
                'foto' => $foto ?: 'Sin foto',
                'last_measurement' => $lastMeasurement,
                'es_primera' => $lastMeasurement ? false : true
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
     * Obtener valor de una fila usando el nombre de columna
     */
    private function getValueFromRow($row, $columnName)
    {
        if (!$columnName) {
            return null;
        }
        return isset($row[$columnName]) ? $row[$columnName] : null;
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

        // Si es numérico, eliminar ceros a la izquierda
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
                'data.*.fecha' => 'required|date',
            ]);

            $importData = $request->data;
            Log::info('Datos a importar', ['total' => count($importData)]);

            $successCount = 0;
            $errorCount = 0;
            $errors = [];

            DB::beginTransaction();

            foreach ($importData as $index => $item) {
                try {
                    $lote = trim($item['lote']);
                    $medidor = trim($item['medidor']);
                    $valormedido = floatval($item['valormedido']);
                    $consumo = isset($item['consumo']) ? floatval($item['consumo']) : 0;
                    $fecha = Carbon::parse($item['fecha'])->startOfDay();
                    $foto = isset($item['foto']) ? trim($item['foto']) : 'Sin foto';

                    Log::debug("Importando lote $lote - medidor $medidor - valor $valormedido - fecha $fecha");

                    // Verificar que el lote exista
                    $user = User::where('lote', $lote)->first();
                    if (!$user) {
                        $errors[] = "Lote $lote: No encontrado en el sistema.";
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

                    // Obtener última medición
                    $lastMeasurement = Medicion::where('lote', $lote)
                        ->orderBy('fecha', 'desc')
                        ->first();

                    // Calcular índices y valores
                    $indice = $lastMeasurement ? $lastMeasurement->indice + 1 : 1;
                    $medidaAnt = $lastMeasurement ? $lastMeasurement->valormedido : 0;
                    $tomaAnt = $lastMeasurement ? $lastMeasurement->fecha : null;

                    // Calcular consumo (si no viene calculado)
                    if ($consumo == 0 && $lastMeasurement) {
                        $consumo = $valormedido - $lastMeasurement->valormedido;
                    }

                    // Si el consumo es negativo, forzar 0 (no permitir consumo negativo)
                    if ($consumo < 0) {
                        $consumo = 0;
                        Log::warning("Consumo negativo forzado a 0", [
                            'lote' => $lote,
                            'valormedido' => $valormedido,
                            'last_value' => $lastMeasurement ? $lastMeasurement->valormedido : 0
                        ]);
                    }

                    // Calcular vencimiento
                    $vencimiento = (clone $fecha)->addDays(30);

                    // Crear la medición
                    Medicion::create([
                        'lote' => $lote,
                        'medidor' => $medidor,
                        'periodo' => 30,
                        'indice' => $indice,
                        'fecha' => $fecha,
                        'vencimiento' => $vencimiento,
                        'tomaant' => $tomaAnt,
                        'medidaant' => $medidaAnt,
                        'valormedido' => $valormedido,
                        'consumo' => $consumo,
                        'inspector' => auth()->user()->name ?? 'admin',
                        'foto' => $foto,
                        'pagado' => 'NO'
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
            
            // Cabeceras
            fputcsv($handle, [
                'Tipo',
                'Fila',
                'Lote',
                'Medidor CSV',
                'Medidor BD',
                'Valor Medido',
                'Consumo Calculado',
                'Fecha',
                'Mensaje'
            ]);

            // Errores
            foreach ($reportData['errors'] as $error) {
                fputcsv($handle, [
                    'ERROR',
                    $error['row'] ?? 'N/A',
                    $error['lote'] ?? 'N/A',
                    $error['medidor_csv'] ?? 'N/A',
                    $error['medidor_bd'] ?? 'N/A',
                    $error['valormedido'] ?? 'N/A',
                    $error['consumo'] ?? 'N/A',
                    $error['fecha'] ?? 'N/A',
                    $error['message'] ?? 'N/A'
                ]);
            }

            // Advertencias
            foreach ($reportData['warnings'] as $warning) {
                fputcsv($handle, [
                    'WARNING',
                    $warning['row'] ?? 'N/A',
                    $warning['lote'] ?? 'N/A',
                    $warning['medidor_csv'] ?? 'N/A',
                    $warning['medidor_bd'] ?? 'N/A',
                    $warning['valormedido'] ?? 'N/A',
                    $warning['consumo'] ?? 'N/A',
                    $warning['fecha'] ?? 'N/A',
                    $warning['message'] ?? 'N/A'
                ]);
            }

            // Resumen
            fputcsv($handle, ['']);
            fputcsv($handle, ['RESUMEN', '', '', '', '', '', '', '', '']);
            fputcsv($handle, ['Total de registros:', $reportData['summary']['total_rows'], '', '', '', '', '', '', '']);
            fputcsv($handle, ['Registros válidos:', $reportData['summary']['valid_rows'], '', '', '', '', '', '', '']);
            fputcsv($handle, ['Errores:', $reportData['summary']['errors_count'], '', '', '', '', '', '', '']);
            fputcsv($handle, ['Advertencias:', $reportData['summary']['warnings_count'], '', '', '', '', '', '', '']);
            fputcsv($handle, ['Duplicados:', $reportData['summary']['duplicates_count'], '', '', '', '', '', '', '']);
            fputcsv($handle, ['Discrepancias de medidor:', $reportData['summary']['medidor_mismatch_count'], '', '', '', '', '', '', '']);
            fputcsv($handle, ['Fecha de generación:', date('Y-m-d H:i:s'), '', '', '', '', '', '', '']);
            fputcsv($handle, ['Usuario:', auth()->user()->name ?? 'admin', '', '', '', '', '', '', '']);

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