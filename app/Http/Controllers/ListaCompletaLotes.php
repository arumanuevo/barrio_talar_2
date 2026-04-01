<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Carbon\Carbon; 
use App\Models\Medicion;

class ListaCompletaLotes extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('listaCompletaLotes');
    }

    public function getLotesData(Request $request)
    {
        // Obtener todos los lotes que tienen medidores (sin paginación)
        $lotes = User::where('lote', '>', 0)->get();

        return response()->json([
            'data' => $lotes
        ]);
    }

    public function getLotesFaltanMedir(Request $request)
    {
        $dias = $request->input('dias', 30);
        $fechaInicio = Carbon::now()->subDays($dias);
    
        $lotes = User::where('lote', '>', 0)->pluck('lote');
        $lotesConMedicionesRecientes = Medicion::whereIn('lote', $lotes)
            ->where('fecha', '>=', $fechaInicio)
            ->pluck('lote')
            ->unique();
    
        $lotesSinMediciones = $lotes->diff($lotesConMedicionesRecientes);
    
        // Ordenar los lotes sin mediciones
        $lotesSinMediciones = $lotesSinMediciones->sort(function ($a, $b) {
            // Extraer el número del lote, eliminando cualquier sufijo no numérico
            preg_match('/\d+/', $a, $matchesA);
            preg_match('/\d+/', $b, $matchesB);
    
            $numA = isset($matchesA[0]) ? intval($matchesA[0]) : 0;
            $numB = isset($matchesB[0]) ? intval($matchesB[0]) : 0;
    
            return $numA - $numB;
        })->values();
    
        return response()->json([
            'mediciones_faltantes' => $lotesSinMediciones
        ]);
    }
}
