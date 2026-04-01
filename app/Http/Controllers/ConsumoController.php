<?php

namespace App\Http\Controllers;

use App\Models\Medicion;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ConsumoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Muestra la vista con el gráfico de consumos por lote
     */
    public function mostrarGraficoConsumos()
    {
        // Obtener todos los lotes únicos
        $lotes = Medicion::select('lote')->distinct()->orderBy('lote')->pluck('lote');

        return view('graficos.consumos', compact('lotes'));
    }

    /**
     * Obtiene los datos de consumo para un lote específico
     */
    public function obtenerDatosConsumo(Request $request)
    {
        $request->validate([
            'lote' => 'required|string',
            'periodo' => 'nullable|string'
        ]);

        $lote = $request->lote;
        $periodo = $request->periodo;

        // Obtener datos de consumo para el lote seleccionado
        $query = Medicion::where('lote', $lote)
            ->orderBy('fecha', 'asc')
            ->select('fecha', 'consumo', 'periodo');

        if ($periodo) {
            $query->where('periodo', $periodo);
        }

        $mediciones = $query->get();

        // Formatear los datos para el gráfico
        $fechas = $mediciones->pluck('fecha')->map(function($fecha) {
            return Carbon::parse($fecha)->format('d/m/Y');
        });

        $consumos = $mediciones->pluck('consumo');
        $periodos = $mediciones->pluck('periodo')->unique()->values();

        return response()->json([
            'fechas' => $fechas,
            'consumos' => $consumos,
            'periodos' => $periodos
        ]);
    }

    public function detectarAnomalias()
    {
        // Obtener todos los lotes con al menos 3 mediciones
        $lotes = Medicion::select('lote')
            ->groupBy('lote')
            ->havingRaw('COUNT(*) >= 3')
            ->pluck('lote');

        $resultados = [];

        foreach ($lotes as $lote) {
            // Obtener todas las mediciones del lote
            $mediciones = Medicion::where('lote', $lote)
                ->orderBy('fecha')
                ->get();

            // Calcular promedio y desviación estándar
            $consumos = $mediciones->pluck('consumo')->toArray();
            $promedio = array_sum($consumos) / count($consumos);

            // Calcular desviación estándar
            $sumaCuadrados = 0;
            foreach ($consumos as $consumo) {
                $sumaCuadrados += pow($consumo - $promedio, 2);
            }
            $desviacionEstandar = sqrt($sumaCuadrados / count($consumos));

            // Identificar mediciones anómalas (más de 2 desviaciones estándar del promedio)
            $anomalias = [];
            foreach ($mediciones as $medicion) {
                if (abs($medicion->consumo - $promedio) > 2 * $desviacionEstandar) {
                    $anomalias[] = [
                        'fecha' => $medicion->fecha,
                        'consumo' => $medicion->consumo,
                        'diferencia' => $medicion->consumo - $promedio
                    ];
                }
            }

            if (!empty($anomalias)) {
                $resultados[$lote] = [
                    'promedio' => $promedio,
                    'desviacion_estandar' => $desviacionEstandar,
                    'anomalias' => $anomalias
                ];
            }
        }

        return response()->json($resultados);
    }

    // En ConsumoController.php
// En ConsumoController.php
/*public function obtenerLotesFaltantes()
{
    try {
        // Usar 'lote' en lugar de 'identificador_lote'
        return response()->json(
            DB::table('lotes_faltantes')->pluck('lote') // <-- Aquí está la corrección
        );
    } catch (\Exception $e) {
        return response()->json([], 500); // Devuelve un array vacío en caso de error
    }
}*/

// En ConsumoController.php
public function obtenerLotesFaltantes()
{
    try {
        // Seleccionar tanto 'lote' como 'medidor' de la tabla
        $lotes = DB::table('lotes_faltantes')
                    ->select('lote', 'medidor') // <-- Incluir 'medidor'
                    ->get()
                    ->toArray();

        // Formatear los datos para que coincidan con la estructura del Excel
        $lotesFormateados = array_map(function($lote) {
            return [
                'lote' => $lote->lote,
                'medidor' => $lote->medidor, // <-- Usar el valor real de la columna 'medidor'
                'periodo' => 'N/A',
                'fecha' => 'N/A',
                'vencimiento' => 'N/A',
                'tomaant' => 'Sin medición',
                'medidaant' => 'Sin medición',
                'valormedido' => 'Sin medición',
                'consumo' => '0',
                'inspector' => 'N/A',
                'foto' => 'Sin foto'
            ];
        }, $lotes);

        return response()->json($lotesFormateados);
    } catch (\Exception $e) {
        return response()->json([], 500);
    }
}




}
