<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medicion;
use Illuminate\Support\Facades\Auth;
class VistaMedicionesLote extends Controller
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
        public function index(Request $request)
        {
            $userId = Auth::user()->id;
            $lote = Auth::user()->lote;

            // Paginamos las mediciones
            $mediciones = Medicion::where('lote', $lote)
                                  ->orderBy('id')
                                  ->paginate(12); // Puedes ajustar el número 15 a la cantidad de elementos por página que desees

            $grafico = ['labels' => [], 'consumos' => []];
            foreach ($mediciones as $medicion) {
                $grafico['labels'][] = (string) $medicion->fecha;
                $grafico['consumos'][] = (float) $medicion->consumo;
            }

            return view('listaMedicionesLote')->with('mediciones', $mediciones)
                                              ->with('grafico', json_encode($grafico));
        }

}
