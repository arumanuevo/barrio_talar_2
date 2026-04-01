<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Sangabriellote;
use Illuminate\Support\Facades\DB;
use Grimzy\LaravelMysqlSpatial\Types\Geometry;
use App\Models\Medicion;
use App\Models\Lote;
//use Geospatial\Geometry\Geometry;
class Mapa extends Controller
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

        return view('mapaGis');
    }

    public function getMapa()
{
    $sangabriellotes = Sangabriellote::all();
    $lote = Lote::all();
    $listaConsumos = [];

    $jsonCadenaCompleta = '[';
    $jsonCadena = '';

    foreach ($lote as $clave => $valor){
        $indice = Medicion::where('lote', $valor->lote)->count();

        if ($indice > 0) {
            $medidaAnt = Medicion::where('lote', $valor->lote)->orderBy('indice', 'desc')->firstOrFail();
            $jsonCadena = '{"id":"' . $medidaAnt->id . '", "lote":"' . $medidaAnt->lote . '","medidor":"' . $medidaAnt->medidor . '", "periodo":"' . $medidaAnt->periodo . '", "fecha":"' . $medidaAnt->fecha . '","vencimiento":"' . $medidaAnt->vencimiento . '","tomaant":"' . $medidaAnt->tomaant . '","medidaant":"' . $medidaAnt->medidaant . '","valormedido":"' . $medidaAnt->valormedido . '","consumo":"' . $medidaAnt->consumo . '","ocupacion":"' . $valor->ocupacion . '", "inspector":"' . $medidaAnt->inspector . '", "pagado":"' . $medidaAnt->pagado . '", "foto":"' . $medidaAnt->foto . '"}';
            $jsonCadenaCompleta .= $jsonCadena.',';
            $listaConsumos[] = json_decode($jsonCadena);
        }
    }

    $cadenaLimpia = mb_substr($jsonCadenaCompleta, 0, -1);
    $jsonCadenaCompleta = $cadenaLimpia.']';

    if ($sangabriellotes->isNotEmpty()) {
        $results = [];
        foreach ($sangabriellotes as $sangabriellote) {
            $geoJson = DB::table('sangabriellotes')
                ->select(DB::raw("ST_AsGeoJSON(SHAPE) as st_asgeojson"), 'id', 'lote')
                ->where('id', $sangabriellote->id)
                ->first();

            if ($geoJson) {
                $result = [
                    'Lote' => $geoJson->lote,
                    'Categoría' => 'Cloaca',
                    'st_asgeojson' => json_decode($geoJson->st_asgeojson),
                ];

                $medidaAnt = $listaConsumos[array_search($geoJson->lote, array_column($listaConsumos, 'lote'))];
                if ($medidaAnt) {
                    $result['consumo'] = $medidaAnt->consumo;
                }

                $results[] = $result;
            }
        }

        return response()->json($results);
    }

    return response()->json(['error' => 'No se encontraron registros'], 404);
}

    /*public function getMapa()
    {
        $sangabriellotes = Sangabriellote::all();
        $mediciones = Medicion::all();
        $lote = Lote::all();
        $listaConsumos = array();

        $jsonCadenaCompleta = '[';
        $jsonCadena ='';

        foreach ($lote as $clave => $valor){

              // $medidaAnt = Medicion::where('lote', $valor->lote)->latest()->take(1)->get();
              //// $medidaAnt = Medicion::where('lote', $valor->lote)->orderBy('indice','desc')->firstOrFail();

               $indice = Medicion::where('lote', $valor->lote)->count(); //veo si el lote tiene alguna medicion
               if ( $indice > 0 ){
                  $medidaAnt = Medicion::where('lote', $valor->lote)->orderBy('indice','desc')->firstOrFail();
                 // $jsonCadena = '{"id":"'.$medidaAnt->id.'", "lote":"'.$medidaAnt->lote.'","medidor":"'.$medidaAnt->medidor.'", "periodo":"'.$medidaAnt->periodo.'", "fecha":"'.$medidaAnt->fecha.'","vencimiento":"'.$medidaAnt->vencimiento.'","tomaant":"'.$medidaAnt->tomaant.'","medidaant":"'.$medidaAnt->medidaant.'","valormedido":"'.$medidaAnt->valormedido.'","consumo":"'.$medidaAnt->consumo.'","ocupacion":"'.$valor->ocupacion.'", "inspector":"'.$medidaAnt->inspector.'", "pagado":"'.$medidaAnt->pagado.'", "foto":"'.$medidaAnt->foto.'"}';
                 $jsonCadena = '{"id":"'.$medidaAnt->id.'", "lote":"'.$medidaAnt->lote.'","medidor":"'.$medidaAnt->medidor.'", "periodo":"'.$medidaAnt->periodo.'", "fecha":"'.$medidaAnt->fecha.'","vencimiento":"'.$medidaAnt->vencimiento.'","tomaant":"'.$medidaAnt->tomaant.'","medidaant":"'.$medidaAnt->medidaant.'","valormedido":"'.$medidaAnt->valormedido.'","consumo":"'.$medidaAnt->consumo.'","ocupacion":"'.$valor->ocupacion.'", "inspector":"'.$medidaAnt->inspector.'", "pagado":"'.$medidaAnt->pagado.'", "foto":"'.$medidaAnt->foto.'"}';
                 $jsonCadenaCompleta .= $jsonCadena.',';
               }
             
        }

        $cadenaLimpia = mb_substr($jsonCadenaCompleta, 0, -1);
        $jsonCadenaCompleta = $cadenaLimpia.']';
        $listaConsumos = json_decode($jsonCadenaCompleta);

        if ($sangabriellotes->isNotEmpty()) {
            $results = [];
            foreach ($sangabriellotes as $sangabriellote) {
                $geoJson = DB::table('sangabriellotes')
                    ->select(DB::raw("ST_AsGeoJSON(SHAPE) as st_asgeojson"), 'id', 'lote')
                    ->where('id', $sangabriellote->id)
                    ->first();
                
                if ($geoJson) {
                    $result = [
                        'Lote' => $geoJson->lote, // Reemplaza esto con el nombre de tu empresa
                        'Categoría' => 'Cloaca', // Reemplaza esto con la categoría adecuada
                        'st_asgeojson' => json_decode($geoJson->st_asgeojson),
                    ];

                    $results[] = $result;
                }
            }
            //dd($results);
            return response()->json($results);
        }

     
        return response()->json(['error' => 'No se encontraron registros'], 404);
    }*/

    
    /*public function getMapa()
    {
        $sangabriellotes = Sangabriellote::first();

        if ($sangabriellotes) {
            // Obtén el campo SHAPE en formato GeoJSON directamente desde MySQL
            $geoJson = DB::table('sangabriellotes')
                ->select(DB::raw("ST_AsGeoJSON(SHAPE) as st_asgeojson"), 'id', 'lote')
                ->where('id', $sangabriellotes->id)
                ->first();

            if ($geoJson) {
                $result = [
                    'Empresa' => 'Leymer S.A.', // Reemplaza esto con el nombre de tu empresa
                    'Categoría' => 'Cloaca', // Reemplaza esto con la categoría adecuada
                    'st_asgeojson' => json_decode($geoJson->st_asgeojson),
                ];

                return response()->json($result);
            }
        }

        // Manejo de caso en el que no se encuentra ningún registro
        return response()->json(['error' => 'Registro no encontrado'], 404);
    }*/



    /*public function getMapa()
    {
        $sangabriellotes = Sangabriellote::first();
        dd($sangabriellotes->SHAPE->toWkt());
        if ($sangabriellotes) {
            $geoJson = [
                'type' => 'Feature',
                'properties' => [
                    'id' => $sangabriellotes->id,
                    'lote' => $sangabriellotes->lote,
                ],
                'geometry' => json_decode($sangabriellotes->SHAPE->toWkt()), // Convierte a WKT y luego a GeoJSON
            ];
    
            return response()->json($geoJson);
        } else {
            // Manejo de caso en el que no se encuentra ningún registro
            return response()->json(['error' => 'Registro no encontrado'], 404);
        }
    }*/

}
