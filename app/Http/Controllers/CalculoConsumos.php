<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use \Carbon\Carbon;
use App\Models\Lote;
use App\Models\Medicion;
use App\Helpers\Genericas\filtroFecha;
use App\Models\SeccionBarrio;
class CalculoConsumos extends Controller
{
    public function index(Request $request )
    {
      $secciones = SeccionBarrio::pluck('nombreseccion', 'id')->prepend('Defina seccion', '');
      return view('calculoConsumos')->with(compact('secciones'));
       // return view('ListadoVentas');
    }
   
  /*  public function calcularDesdeHasta(Request $request )
    {

        $diaDesde = new Carbon($request->diaDesde);
        $diaHasta = new Carbon($request->diaHasta);
        
        $filtroxFecha = FiltroFecha::filtroFecha($diaDesde,$diaHasta);
       
        return $filtroxFecha;

       // return view('ListadoVentas');
    }*/
}
