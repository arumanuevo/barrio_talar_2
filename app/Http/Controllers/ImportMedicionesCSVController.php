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
                'size' => $file->getSize(),
                'mime' => $file->getMimeType()
            ]);

            $content = file_get_contents($file->getPathname());
            Log::info('Contenido del archivo (primeros 500 caracteres):', [
                'content' => substr($content, 0, 500)
            ]);

            // ✅ CORREGIDO: Usar fgetcsv para manejar correctamente el delimitador
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
                // Limpiar cada campo (eliminar comillas y espacios)
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
                    'message' => 'El archivo CSV está vacío o no tiene datos. Se encontraron ' . count($lines) . ' filas.'
                ], 400);
            }

            // Obtener encabezados
            $headers = $lines[0];
            Log::info('Encabezados detectados', ['headers' => $headers]);

            // Normalizar encabezados (eliminar caracteres especiales)
            $headers = array_map(function($h) {
                return strtolower(trim($h));
            }, $headers);

            $data = [];
            // Procesar cada fila
            for ($i = 1; $i < count($lines); $i++) {
                if (empty($lines[$i]) || count($lines[$i]) < 2) continue;
                
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = isset($lines[$i][$index]) ? trim($lines[$i][$index]) : '';
                }
                $data[] = $row;
            }

            Log::info('Datos procesados', ['total_filas' => count($data)]);

            // Si no hay datos, devolver error
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
            'existing_users' => [],
            'missing_users' => []
        ];

        foreach ($data as $index => $row) {
            $rowNumber = $index + 2; // +2 por encabezado y offset

            // Normalizar claves (algunas pueden venir con BOM o espacios)
            $normalizedRow = [];
            foreach ($row as $key => $value) {
                $normalizedRow[trim($key)] = $value;
            }
            $row = $normalizedRow;

            // Obtener valores con diferentes nombres de columna posibles
            $lote = $this->getValue($row, ['lote', 'LOTE', 'lotes', 'LOTES']);
            $medidor = $this->getValue($row, ['medidor', 'MEDIDOR', 'medidores', 'MEDIDORES']);
            $consumo = $this->getValue($row, ['consumo', 'CONSUMO', 'valor', 'VALOR', 'valormedido', 'VALORMEDIDO']);
            $fechaMedicion = $this->getValue($row, ['fecha_medicion', 'FECHA_MEDICION', 'fecha', 'FECHA', 'fechamedicion', 'FECHAMEDICION']);
            $foto = $this->getValue($row, ['foto', 'FOTO', 'fotos', 'FOTOS']);

            // Log de cada fila para depuración
            Log::debug("Fila $rowNumber: " . json_encode([
                'lote' => $lote,
                'medidor' => $medidor,
                'consumo' => $consumo,
                'fecha' => $fechaMedicion,
                'foto' => $foto
            ]));

            // Validaciones básicas
            if (empty($lote)) {
                $report['errors'][] = "Fila $rowNumber: Lote vacío";
                continue;
            }

            if (empty($medidor)) {
                $report['errors'][] = "Fila $rowNumber: Medidor vacío";
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

            // Limpiar lote (eliminar ceros a la izquierda)
            $lote = $this->cleanLote($lote);

            // Buscar usuario
            $user = User::where('lote', $lote)->first();
            
            if (!$user) {
                $report['missing_users'][] = [
                    'row' => $rowNumber,
                    'lote' => $lote,
                    'medidor' => $medidor
                ];
                $report['errors'][] = "Fila $rowNumber: Lote $lote no encontrado en el sistema";
                continue;
            }

            if ($user->medidor != $medidor) {
                $report['errors'][] = "Fila $rowNumber: Medidor $medidor no coincide con el registrado ({$user->medidor}) para el lote $lote";
                continue;
            }

            // Verificar fecha
            try {
                $fecha = Carbon::parse($fechaMedicion)->startOfDay();
            } catch (\Exception $e) {
                $report['errors'][] = "Fila $rowNumber: Fecha inválida ($fechaMedicion)";
                continue;
            }

            // Obtener la última medición del lote
            $lastMeasurement = Medicion::where('lote', $lote)
                ->orderBy('fecha', 'desc')
                ->first();

            // Verificar duplicado exacto
            $exists = Medicion::where('lote', $lote)
                ->where('fecha', $fecha)
                ->exists();

            if ($exists) {
                $report['duplicates']++;
                $report['warnings'][] = "Fila $rowNumber: Ya existe medición para el lote $lote en fecha $fechaMedicion";
                continue;
            }

            // Calcular nuevo valor
            if ($lastMeasurement) {
                $lastValue = $lastMeasurement->valormedido;
                $newValue = $lastValue + $consumoFloat;
                
                // Verificar si el nuevo valor es menor que el anterior
                if ($newValue < $lastValue) {
                    $report['warnings'][] = "Fila $rowNumber: El valor calculado ($newValue) es menor que el último registrado ($lastValue) para el lote $lote";
                }
            } else {
                // Si no hay mediciones previas, el consumo debería ser 0
                if ($consumoFloat > 0) {
                    $report['warnings'][] = "Fila $rowNumber: Primera medición del lote $lote con consumo > 0 ($consumoFloat)";
                }
                $newValue = $consumoFloat;
            }

            // Guardar datos válidos
            $report['valid_data'][] = [
                'lote' => $lote,
                'medidor' => $medidor,
                'consumo' => $consumoFloat,
                'fecha' => $fecha->format('Y-m-d'),
                'foto' => $foto ?: 'Sin foto',
                'last_value' => $lastMeasurement ? $lastMeasurement->valormedido : 0,
                'new_value' => $newValue,
                'inspector' => 'admin',
                'pagado' => 'NO'
            ];

            $report['new_measurements']++;
        }

        // Calcular estadísticas
        $report['summary'] = [
            'total_rows' => $report['total'],
            'valid_rows' => count($report['valid_data']),
            'errors_count' => count($report['errors']),
            'warnings_count' => count($report['warnings']),
            'duplicates_count' => $report['duplicates']
        ];

        return $report;
    }

    /**
     * Obtener valor de un array con múltiples posibles claves
     */
    private function getValue($array, $keys)
    {
        foreach ($keys as $key) {
            if (isset($array[$key]) && !empty($array[$key])) {
                return $array[$key];
            }
        }
        return null;
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
                'data.*.consumo' => 'required|numeric',
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
                    $consumo = floatval($item['consumo']);
                    $fecha = Carbon::parse($item['fecha'])->startOfDay();
                    $foto = isset($item['foto']) ? trim($item['foto']) : 'Sin foto';

                    Log::debug("Importando $lote - $medidor - $consumo - $fecha");

                    // Verificar que el lote exista
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

                    $indice = $lastMeasurement ? $lastMeasurement->indice + 1 : 1;
                    $medidaAnt = $lastMeasurement ? $lastMeasurement->valormedido : 0;
                    $tomaAnt = $lastMeasurement ? $lastMeasurement->fecha : null;
                    $valor = $lastMeasurement ? ($lastMeasurement->valormedido + $consumo) : $consumo;

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
                        'valormedido' => $valor,
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
                    $errors[] = "Item $index: " . $e->getMessage();
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
                'Medidor',
                'Consumo',
                'Fecha',
                'Mensaje'
            ]);

            // Errores
            foreach ($reportData['errors'] as $error) {
                fputcsv($handle, [
                    'ERROR',
                    $error['row'] ?? 'N/A',
                    $error['lote'] ?? 'N/A',
                    $error['medidor'] ?? 'N/A',
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
                    $warning['medidor'] ?? 'N/A',
                    $warning['consumo'] ?? 'N/A',
                    $warning['fecha'] ?? 'N/A',
                    $warning['message'] ?? 'N/A'
                ]);
            }

            // Resumen
            fputcsv($handle, ['']);
            fputcsv($handle, ['RESUMEN', '', '', '', '', '', '']);
            fputcsv($handle, ['Total de registros:', $reportData['summary']['total_rows'], '', '', '', '', '']);
            fputcsv($handle, ['Registros válidos:', $reportData['summary']['valid_rows'], '', '', '', '', '']);
            fputcsv($handle, ['Errores:', $reportData['summary']['errors_count'], '', '', '', '', '']);
            fputcsv($handle, ['Advertencias:', $reportData['summary']['warnings_count'], '', '', '', '', '']);
            fputcsv($handle, ['Duplicados:', $reportData['summary']['duplicates_count'], '', '', '', '', '']);
            fputcsv($handle, ['Fecha de generación:', date('Y-m-d H:i:s'), '', '', '', '', '']);
            fputcsv($handle, ['Usuario:', auth()->user()->name ?? 'admin', '', '', '', '', '']);

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