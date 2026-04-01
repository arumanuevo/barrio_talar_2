<?php

namespace App\Helpers\Genericas;

use Illuminate\Http\Request;
Use \Carbon\Carbon;
use App\Models\Lote;
use App\Models\Medicion;
use Illuminate\Support\Facades\Log;

class FiltroFecha 
{
   public static function filtroFecha($diaDesde, $diaHasta){
      
        $carbonDiaDesde = new Carbon($diaDesde);
        $carbonDiaHasta = new Carbon($diaHasta);
        
        $mediciones = Medicion::orderBy('lote','asc')->get();

        $fechaMedicion = new Carbon();
        $jsonCadenaCompleta = '[';
        $jsonCadena = '';
        $listaDesdeHasta = '';
        $sumario = 0;
        $primeraPasada = true;
        $lote ='';
 
        foreach ($mediciones as $clave => $valor){
            $fechaMedicion = $valor->fecha;
            if ($fechaMedicion > $carbonDiaDesde && $fechaMedicion < $carbonDiaHasta){
                if($primeraPasada ){
                    $lote = $valor->lote;
                    $primeraPasada = false;
                }

                if($lote == $valor->lote){
                    $sumario += $valor->consumo;
                }else{
                    $lote = $valor->lote;
                    $sumario = $valor->consumo;
                }

                $jsonCadena = '{"id":"'.$valor->id.'", "lote":"'.$valor->lote.'","medidor":"'.$valor->medidor.'", 
                    "periodo":"'.$valor->periodo.'", "fecha":"'.$valor->fecha.'","vencimiento":"'.$valor->vencimiento.'",
                    "tomaant":"'.$valor->tomaant.'","medidaant":"'.$valor->medidaant.'","valormedido":"'.$valor->valormedido.'",
                    "sumario":"'.$sumario.'","consumo":"'.$valor->consumo.'", "inspector":"'.$valor->inspector.'", 
                    "foto":"'.$valor->foto.'"}';
                $jsonCadenaCompleta .= $jsonCadena.',';
            }
        }
        $cadenaLimpia = mb_substr($jsonCadenaCompleta, 0, -1);
       
       
        $jsonCadenaCompleta = $cadenaLimpia.']';
        
        $listaDesdeHasta = json_decode($jsonCadenaCompleta);

        /*if (json_last_error() !== JSON_ERROR_NONE) {
            echo 'JSON decode error: ' . json_last_error_msg();
            dd('Error decoding JSON');
        }*/
    
       // dd($listaDesdeHasta);

        $respuesta = response()->json(array('listaConsumos'=>$listaDesdeHasta), 200);
        
        return $respuesta;
   } 

  /* public static function filtroFecha($diaDesde, $diaHasta){
    $carbonDiaDesde = new Carbon($diaDesde);
    $carbonDiaHasta = new Carbon($diaHasta);

    $mediciones = Medicion::orderBy('lote', 'asc')->get();
    $fechaMedicion = new Carbon();
    $resultArray = [];
    $sumario = 0;
    $primeraPasada = true;
    $lote = '';

    foreach ($mediciones as $clave => $valor){
        $fechaMedicion = $valor->fecha;
        if ($fechaMedicion > $carbonDiaDesde && $fechaMedicion < $carbonDiaHasta){
            if ($primeraPasada){
                $lote = $valor->lote;
                $primeraPasada = false;
            }

            if ($lote == $valor->lote){
                $sumario += $valor->consumo;
            } else {
                $lote = $valor->lote;
                $sumario = $valor->consumo;
            }

            $resultArray[] = [
                'id' => $valor->id,
                'lote' => $valor->lote,
                'medidor' => $valor->medidor,
                'periodo' => $valor->periodo,
                'fecha' => $valor->fecha,
                'vencimiento' => $valor->vencimiento,
                'tomaant' => $valor->tomaant,
                'medidaant' => $valor->medidaant,
                'valormedido' => $valor->valormedido,
                'sumario' => $sumario,
                'consumo' => $valor->consumo,
                'inspector' => $valor->inspector,
                'foto' => $valor->foto
            ];
        }
    }

    // Utiliza json_encode para convertir el array a JSON
    $jsonCadenaCompleta = json_encode($resultArray);
    
    // Verifica si json_encode falló
    if ($jsonCadenaCompleta === false) {
        echo 'JSON encode error: ' . json_last_error_msg();
        dd('Error encoding JSON');
    }

    $listaDesdeHasta = json_decode($jsonCadenaCompleta);
    if (json_last_error() !== JSON_ERROR_NONE) {
        echo 'JSON decode error: ' . json_last_error_msg();
        dd('Error decoding JSON');
    }

    dd($listaDesdeHasta);

    $respuesta = response()->json(array('listaConsumos' => $listaDesdeHasta), 200);
    
    return $respuesta;
}*/

    
}
