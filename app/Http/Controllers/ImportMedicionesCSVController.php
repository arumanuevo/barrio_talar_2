<?php
// app/Http/Controllers/ImportMedicionesCSVController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            $request->validate([
                'file' => 'required|file|mimes:csv,txt'
            ]);

            $file = $request->file('file');
            $content = file_get_contents($file->getPathname());
            $lines = array_map('str_getcsv', explode("\n", trim($content)));

            if (empty($lines) || count($lines) < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo CSV está vacío o no tiene datos.'
                ], 400);
            }

            // Obtener encabezados
            $headers = $lines[0];
            $data = [];

            // Procesar cada fila
            for ($i = 1; $i < count($lines); $i++) {
                if (empty($lines[$i]) || count($lines[$i]) < 3) continue;
                
                $row = [];
                foreach ($headers as $index => $header) {
                    $row[$header] = isset($lines[$i][$index]) ? trim($lines[$i][$index]) : '';
                }
                $data[] = $row;
            }

            // Analizar los datos
            $report = $this->analyzeData($data);

            return response()->json([
                'success' => true,
                'data' => $report
            ]);

        } catch (\Exception $e) {
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

            // Validar lote
            $lote = isset($row['lote']) ? trim($row['lote']) : '';
            $medidor = isset($row['medidor']) ? trim($row['medidor']) : '';
            $consumo = isset($row['consumo']) ? floatval(str_replace(',', '.', trim($row['consumo']))) : null;
            $fechaMedicion = isset($row['fecha_medicion']) ? trim($row['fecha_medicion']) : '';
            $foto = isset($row['foto']) ? trim($row['foto']) : null;

            // Validaciones básicas
            if (empty($lote)) {
                $report['errors'][] = "Fila $rowNumber: Lote vacío";
                continue;
            }

            if (empty($medidor)) {
                $report['errors'][] = "Fila $rowNumber: Medidor vacío";
                continue;
            }

            if ($consumo === null || $consumo < 0) {
                $report['errors'][] = "Fila $rowNumber: Consumo inválido ($consumo)";
                continue;
            }

            if (empty($fechaMedicion)) {
                $report['errors'][] = "Fila $rowNumber: Fecha de medición vacía";
                continue;
            }

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
                $report['errors'][] = "Fila $rowNumber: Medidor $medidor no coincide con el registrado ({$user->medidor})";
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

            // Verificar consumo negativo
            if ($lastMeasurement) {
                $lastValue = $lastMeasurement->valormedido;
                $newValue = $lastValue + $consumo;
                
                // Verificar si el nuevo valor es menor que el anterior
                if ($newValue < $lastValue) {
                    $report['warnings'][] = "Fila $rowNumber: El valor calculado ($newValue) es menor que el último registrado ($lastValue) para el lote $lote";
                }
            } else {
                // Si no hay mediciones previas, el consumo debería ser 0
                if ($consumo > 0) {
                    $report['warnings'][] = "Fila $rowNumber: Primera medición del lote $lote con consumo > 0 ($consumo)";
                }
                $newValue = $consumo;
            }

            // Guardar datos válidos
            $report['valid_data'][] = [
                'lote' => $lote,
                'medidor' => $medidor,
                'consumo' => $consumo,
                'fecha' => $fecha->format('Y-m-d'),
                'foto' => $foto,
                'last_value' => $lastMeasurement ? $lastMeasurement->valormedido : 0,
                'new_value' => $lastMeasurement ? ($lastMeasurement->valormedido + $consumo) : $consumo,
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
     * Importa los datos válidos a la base de datos
     */
    public function importCSV(Request $request)
    {
        try {
            $request->validate([
                'data' => 'required|array',
                'data.*.lote' => 'required|string',
                'data.*.medidor' => 'required|string',
                'data.*.consumo' => 'required|numeric',
                'data.*.fecha' => 'required|date',
            ]);

            $importData = $request->data;
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
                    $errors[] = "Item $index: " . $e->getMessage();
                    $errorCount++;
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

    /**
     * Descarga el informe de la importación
     */
    public function downloadReport(Request $request)
    {
        try {
            $reportData = $request->input('report_data');
            
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
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el informe: ' . $e->getMessage()
            ], 500);
        }
    }
}