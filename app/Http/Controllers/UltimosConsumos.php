<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lote;
use App\Models\Medicion;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class UltimosConsumos extends Controller
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

        public function ultimosConsumos(Request $request)
        {
            $lotes = User::all();
            $ultimasMediciones = [];
            
            foreach ($lotes as $lote) {
                $ultimaMedicion = Medicion::where('lote', $lote->lote)
                    ->orderBy('indice', 'desc')
                    ->first();
            
                if ($ultimaMedicion) {
                    $seccionLote = $ultimaMedicion->seccion . '-' . $ultimaMedicion->lote;
                    $ultimasMediciones[$seccionLote] = [
                        'id' => $ultimaMedicion->id,
                        'lote' => $ultimaMedicion->lote,
                        'medidor' => $ultimaMedicion->medidor,
                        'periodo' => $ultimaMedicion->periodo,
                        'fecha' => $ultimaMedicion->fecha,
                        'vencimiento' => $ultimaMedicion->vencimiento,
                        'tomaant' => $ultimaMedicion->tomaant,
                        'medidaant' => $ultimaMedicion->medidaant,
                        'valormedido' => $ultimaMedicion->valormedido,
                        'consumo' => $ultimaMedicion->consumo,
                        'ocupacion' => $lote->ocupacion,
                        'inspector' => $ultimaMedicion->inspector,
                        'foto' => $ultimaMedicion->foto,
                    ];
                }
            }

            // Convertir el array en una colección
            $ultimasMedicionesCollection = collect($ultimasMediciones);

            // Definir la paginación
            $perPage = 12;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $currentPageItems = $ultimasMedicionesCollection->slice(($currentPage - 1) * $perPage, $perPage)->all();
            $paginatedItems = new LengthAwarePaginator($currentPageItems, $ultimasMedicionesCollection->count(), $perPage);
            $paginatedItems->setPath($request->url());

            return view('ultimosConsumos')->with('ultimasMediciones', $paginatedItems);
        }
        

        /*public function ultimosConsumos(Request $request)
         {
            $lotes = Lote::all();
            $listaConsumos = array();

            $jsonCadenaCompleta = '[';
            $jsonCadena = '';

            foreach ($lotes as $lote) {
               $ultimaMedicion = Medicion::where('lote', $lote->lote)
                     ->where('seccion', $lote->seccion)
                     ->orderBy('indice', 'desc')
                     ->first();

               if ($ultimaMedicion) {
                     $jsonCadena = '{"id":"' . $ultimaMedicion->id . '", "lote":"' . $ultimaMedicion->lote . '","medidor":"' . $ultimaMedicion->medidor . '", "periodo":"' . $ultimaMedicion->periodo . '", "fecha":"' . $ultimaMedicion->fecha . '","vencimiento":"' . $ultimaMedicion->vencimiento . '","tomaant":"' . $ultimaMedicion->tomaant . '","medidaant":"' . $ultimaMedicion->medidaant . '","valormedido":"' . $ultimaMedicion->valormedido . '","consumo":"' . $ultimaMedicion->consumo . '","ocupacion":"' . $lote->ocupacion . '", "inspector":"' . $ultimaMedicion->inspector . '", "pagado":"' . $ultimaMedicion->pagado . '", "foto":"' . $ultimaMedicion->foto . '"}';
                     $jsonCadenaCompleta .= $jsonCadena . ',';
               }
            }

            $cadenaLimpia = mb_substr($jsonCadenaCompleta, 0, -1);
            $jsonCadenaCompleta = $cadenaLimpia . ']';

            $listaConsumos = json_decode($jsonCadenaCompleta);
            dd($listaConsumos);

            return view('ultimosConsumos')->with('mediciones', $listaConsumos);
         }*/


}


