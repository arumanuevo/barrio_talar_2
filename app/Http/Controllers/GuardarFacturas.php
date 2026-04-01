<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use \Carbon\Carbon;
use App\Models\Lote;
use App\Models\Medicion;
use App\Models\Factura;
use App\Http\Controllers\CalculoConsumos;
use Illuminate\Support\Facades\Route;
use App\Helpers\Genericas\filtroFecha;

class GuardarFacturas extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
      //  $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postGuardarFacturas(Request $request)
    {
        return 'santiago';
    }
    public function getGuardarFacturas(Request $request)
    {
        /*$valorFijo = $request->valorFijo;
        $diaDesde = new Carbon($request->diaDesde);
        $diaHasta = new Carbon($request->diaHasta);
        $lotesFiltrados = FiltroFecha::filtroFecha($diaDesde,$diaHasta);*/
        $facturas = $request->datosFacturas;
        $codigoAysa = $request->facturaAysa;
        $diaDesde = $request->diaDesde;
        $diaHasta = $request->diaHasta;
        $vencimientoAysa = $request->vencimientoAysa;
        $jsonenc = json_encode($facturas[0]);
        $jsondec = json_decode($jsonenc);

        foreach ($facturas as $clave => $valor){
            $jsonenc = json_encode($valor);
            $jsondec = json_decode($jsonenc);
           
            Factura::updateOrCreate(
                ['codaysa' =>  $codigoAysa,'lote' => $jsondec->lote ],
                [
                    'lote' => $jsondec->lote,
                    'medidor' => $jsondec->medidor,
                    'fdesde' => $diaDesde,
                    'fhasta' => $diaHasta,
                    'medant' => $jsondec->medidaant,
                    'ultmedida' => $jsondec->valormedido,
                    'consumo' => $jsondec->consumo,
                    'sumario' => $jsondec->sumario,
                    'valxconsumo' => $jsondec->valorxConsumo,
                    'total' => $jsondec->total,
                    'foto' => $jsondec->foto,
                    'codaysa' => $codigoAysa,
                    'venaysa' => $vencimientoAysa,
                    ]
            );
         /*  Factura::create([
            'lote' => $jsondec->lote,
            'medidor' => $jsondec->medidor,
            'fmedicion' => $jsondec->fecha,
            'fanterior' => $jsondec->tomaant,
            'medant' => $jsondec->medidaant,
            'ultmedida' => $jsondec->valormedido,
            'consumo' => $jsondec->consumo,
            'sumario' => $jsondec->sumario,
            'valxconsumo' => $jsondec->valorxConsumo,
            'total' => $jsondec->total,
            'foto' => $jsondec->foto,
            'codaysa' => $codigoAysa,
            'venaysa' => $vencimientoAysa,
            ]);*/

        }
     
        
        return 'Facturas Almacenadas';
    }
}
