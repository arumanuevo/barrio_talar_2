<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Medicion;
use Carbon\Carbon;
use Shuchkin\SimpleXLSX;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
     * Analiza el archivo Excel y devuelve las hojas y columnas detectadas.
     */
    public function analyzeFile(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getPathname();

            // Usar SimpleXLSX para leer el archivo
            $xlsx = SimpleXLSX::parse($path);
            if (!$xlsx) {
                throw new \Exception('No se pudo leer el archivo: ' . SimpleXLSX::parseError());
            }

            // Obtener nombres de hojas
            $sheetNames = $xlsx->sheetNames();

            // Tomar la primera hoja para obtener encabezados
            $rows = $xlsx->rows(0); // hoja índice 0
            if (empty($rows)) {
                throw new \Exception('El archivo está vacío.');
            }

            $headers = $rows[0]; // primera fila como encabezados
            // Limpiar encabezados (eliminar espacios, etc.)
            $headers = array_map('trim', $headers);

            // Obtener datos de muestra (hasta 5 filas)
            $sampleData = [];
            for ($i = 1; $i <= min(5, count($rows) - 1); $i++) {
                $sampleData[] = $rows[$i];
            }

            return response()->json([
                'success' => true,
                'sheets' => $sheetNames,
                'headers' => $headers,
                'sample_data' => $sampleData
            ]);
        } catch (\Exception $e) {
            Log::error('Error al analizar archivo: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al leer el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Previsualiza los datos del archivo y aplica validaciones.
     */
    public function previewImport(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'sheet_index' => 'required|integer|min:0',
            'column_mapping' => 'required|array',
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getPathname();
            $xlsx = SimpleXLSX::parse($path);
            if (!$xlsx) {
                throw new \Exception('No se pudo leer el archivo: ' . SimpleXLSX::parseError());
            }

            $sheetIndex = $request->sheet_index;
            $rows = $xlsx->rows($sheetIndex);
            if (empty($rows) || count($rows) < 2) {
                throw new \Exception('La hoja seleccionada está vacía o solo tiene encabezados.');
            }

            $headers = $rows[0];
            $headers = array_map('trim', $headers);
            $mapping = $request->column_mapping;

            // Validar que el mapeo tenga lote, medidor y fechas
            if (!isset($mapping['lote']) || !isset($mapping['medidor']) || !isset($mapping['fechas'])) {
                throw new \Exception('El mapeo de columnas está incompleto. Debe incluir lote, medidor y fechas.');
            }

            // Procesar filas desde la 2 en adelante
            $preview = [];
            $errors = [];
            $allErrors = [];

            for ($rowIdx = 1; $rowIdx < count($rows); $rowIdx++) {
                $row = $rows[$rowIdx];
                $lote = trim($row[$mapping['lote']] ?? '');
                $medidor = trim($row[$mapping['medidor']] ?? '');

                // Validar lote y medidor
                if (empty($lote) || empty($medidor)) {
                    $allErrors[] = "Fila " . ($rowIdx + 1) . ": Lote o medidor vacío.";
                    continue;
                }

                $user = User::where('lote', $lote)->first();
                if (!$user) {
                    $allErrors[] = "Fila " . ($rowIdx + 1) . ": Lote $lote no encontrado en usuarios.";
                    continue;
                }
                if ($user->medidor != $medidor) {
                    $allErrors[] = "Fila " . ($rowIdx + 1) . ": El medidor $medidor no coincide con el registrado para el lote $lote.";
                    continue;
                }

                // Procesar fechas (columnas mapeadas)
                $mediciones = [];
                foreach ($mapping['fechas'] as $colIndex => $fechaLabel) {
                    if (!isset($row[$colIndex])) continue;
                    $valor = trim($row[$colIndex]);
                    if ($valor === '' || $valor === null) continue;
                    if (!is_numeric($valor)) {
                        $allErrors[] = "Fila " . ($rowIdx + 1) . ": Valor no numérico en columna '$fechaLabel'.";
                        continue 2;
                    }

                    // Parsear fecha desde el encabezado
                    $fecha = $this->parseDateFromHeader($fechaLabel);
                    if (!$fecha) {
                        $allErrors[] = "Fila " . ($rowIdx + 1) . ": No se pudo interpretar la fecha para la columna '$fechaLabel'.";
                        continue 2;
                    }

                    $mediciones[] = [
                        'fecha' => $fecha,
                        'valor' => (float) $valor
                    ];
                }

                if (empty($mediciones)) {
                    $allErrors[] = "Fila " . ($rowIdx + 1) . ": No se encontraron mediciones válidas para el lote $lote.";
                    continue;
                }

                // Ordenar mediciones por fecha
                usort($mediciones, function($a, $b) {
                    return $a['fecha']->timestamp - $b['fecha']->timestamp;
                });

                // Validar secuencia de fechas y valores
                $previousValue = 0;
                $previousDate = null;
                $valid = true;
                foreach ($mediciones as $idx => $med) {
                    if ($previousDate && $med['fecha'] <= $previousDate) {
                        $allErrors[] = "Fila " . ($rowIdx + 1) . ": Fecha no creciente para el lote $lote.";
                        $valid = false;
                        break;
                    }
                    if ($med['valor'] < $previousValue) {
                        $allErrors[] = "Fila " . ($rowIdx + 1) . ": Valor {$med['valor']} menor que el anterior $previousValue para lote $lote.";
                        $valid = false;
                        break;
                    }
                    $previousDate = $med['fecha'];
                    $previousValue = $med['valor'];
                }

                if (!$valid) continue;

                $preview[] = [
                    'lote' => $lote,
                    'medidor' => $medidor,
                    'mediciones' => $mediciones,
                    'estado' => 'OK'
                ];
            }

            return response()->json([
                'success' => true,
                'preview' => $preview,
                'errors' => $allErrors
            ]);

        } catch (\Exception $e) {
            Log::error('Error en previewImport: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Parsea una fecha desde un encabezado como "NOVIEMBRE 03/12" o "DICIEMBRE 02/01/2026".
     * Devuelve un objeto Carbon o null.
     */
    private function parseDateFromHeader($header)
    {
        // Intentar extraer fecha en formato "03/12" o "02/01/2026"
        $pattern = '/(\d{1,2})\/(\d{1,2})(?:\/(\d{4}))?/';
        if (preg_match($pattern, $header, $matches)) {
            $day = (int) $matches[1];
            $month = (int) $matches[2];
            $year = isset($matches[3]) ? (int) $matches[3] : null;
            if (!$year) {
                // Asumir año basado en mes (si mes >= 11, año 2025; sino 2026)
                if ($month >= 11) {
                    $year = 2025;
                } else {
                    $year = 2026;
                }
            }
            try {
                return Carbon::createFromDate($year, $month, $day)->startOfDay();
            } catch (\Exception $e) {
                return null;
            }
        }
        return null;
    }

    /**
     * Ejecuta la importación de las mediciones validadas.
     */
    public function import(Request $request)
    {
        $request->validate([
            'data' => 'required|array',
        ]);

        $importData = $request->data;
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            foreach ($importData as $item) {
                $lote = $item['lote'];
                $medidor = $item['medidor'];
                $mediciones = $item['mediciones'];

                // Obtener la última medición existente para este lote
                $lastMedicion = Medicion::where('lote', $lote)
                                        ->orderBy('fecha', 'desc')
                                        ->first();

                $indice = $lastMedicion ? $lastMedicion->indice + 1 : 1;
                $medidaAnt = $lastMedicion ? $lastMedicion->valormedido : 0;
                $tomaAnt = $lastMedicion ? $lastMedicion->fecha : null;

                foreach ($mediciones as $med) {
                    $fecha = $med['fecha'];
                    $valor = $med['valor'];

                    // Verificar duplicado
                    $exists = Medicion::where('lote', $lote)
                                        ->where('fecha', $fecha)
                                        ->exists();
                    if ($exists) {
                        $errors[] = "Lote $lote: Ya existe medición para la fecha $fecha. Se omite.";
                        $errorCount++;
                        continue;
                    }

                    $consumo = $valor - $medidaAnt;
                    if ($consumo < 0) {
                        $errors[] = "Lote $lote: Consumo negativo ($consumo) para la fecha $fecha. Se omite.";
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
                        'inspector' => auth()->user()->name ?? 'admin',
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
            Log::error('Error en import: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage()
            ], 500);
        }
    }
}