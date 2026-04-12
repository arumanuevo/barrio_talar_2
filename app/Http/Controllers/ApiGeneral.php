<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
Use \Carbon\Carbon;
use App\Models\Lote;
use App\Models\Medicion;
use App\Helpers\genericas\filtroFecha;
use Illuminate\Support\Facades\Auth;
use App\Models\Factura;
//use App\Models\SeccionBarrio;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class ApiGeneral extends Controller
{

    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
  /*  public function __construct()
    {
        $this->middleware('auth');
    }*/
    
    public function calcularDesdeHasta(Request $request )
    {

        $diaDesde = new Carbon($request->diaDesde);
        $diaHasta = new Carbon($request->diaHasta);

        //$seccion = $request->seccion;
        $filtroxFecha = filtroFecha::filtroFecha($diaDesde, $diaHasta);
       
        return $filtroxFecha;

       // return view('ListadoVentas');
    }

    public function getFacturasGraf(Request $request )
    {
       /* $userId = Auth::user()->id;
        $lote = Auth::user()->lote;
       $usuario = Auth::user();*/
      /*  $facturasLote = Factura::where('lote', $lote)->get();

        return $facturasLote;*/
        $lote = $request->lote;
        $seccion = $request->seccion;
        $facturasLote = Factura::where('lote', $lote)
                        ->get();
        return $facturasLote;
     
    }

    public function getLotes(Request $request )
    {
      
        //$lotes = Lote::all();
        $lotes = User::where('lote','<>', 0)->get();
        $respuesta = response()->json(array('msg'=> $lotes), 200);
        return $respuesta;
    }

    public function getTodasMediciones(Request $request )
    {
        $mediciones = Medicion::all();
       // $lotes = Lote::where('medidor','<>', 'N/A')->get();
       // $respuesta = response()->json(array('msg'=> $mediciones), 200);
       // return $respuesta;
        return $mediciones;
    }

    /*public function getLoteSeccion(Request $request)
    {
       // $lotes = Lote::all();
        $seccion = $request->numSeccion; 

        $lotes = Lote::where('seccion', $seccion)->get();
        $definaLote = new \stdClass();
        $definaLote->id = null;  // O algún valor único que represente "Defina Lote"
        $definaLote->lote = 'N/A';
        $lotes->prepend($definaLote);
        // $lotes = Lote::where('medidor','<>', 'N/A')->get();
        
        return response()->json($lotes);
    }*/


    public function getMedicionesLote(Request $request )
    {
        $mediciones = Medicion::all();
        //$lotes = $query->paginate(15);
       // $lotes = Lote::where('medidor','<>', 'N/A')->get();
       // $respuesta = response()->json(array('msg'=> $mediciones), 200);
       // return $respuesta;
        return $mediciones;
    }

   

    public function getMedidor(Request $request )
    {
      
        $numLote = $request->numLote; 
        $seccion = $request->seccion;
        
        $lotes = User::where('lote', $numLote)
                      ->first();
        //$tokenAcceso = $usuario->createToken('TokenName')->plainTextToken;
       // dd($token);
        $ultimaMedicion = Medicion::where('lote', $numLote)
                                    ->orderBy('id','desc')->first();
     
        $respuesta = response()->json(array('msg'=> $lotes,'ultimaMedicion'=>$ultimaMedicion), 200);
        return $respuesta;
    }
    
    public function postBorrarMedicion(Request $request){
      $id = $request->id;
      $res = Medicion::where('id',$id)->delete();
      $respuesta = response()->json(array('msg'=> 'exito'), 200);
      return $respuesta; 
     
    }

    public function postSanti(Request $request){
        $facturas = $request->datos;
        $codigoAysa = $request->factN;
        $diaDesde = $request->Desde;
        $diaHasta = $request->Hasta;
        $vencimientoAysa = $request->vAysa;
        $jsonenc = json_encode($facturas[0]);
        $jsondec = json_decode($jsonenc);
        $respuesta = response()->json(array('msg'=> $vencimientoAysa), 200);
        return $respuesta; 
     
    }

    public function postMed(Request $request)
    {
        // Log inicial de la solicitud completa
        \Log::info('postMed - Solicitud recibida', [
            'request_all' => $request->all(),
            'request_input' => $request->input(),
            'request_json' => $request->json()->all()
        ]);
    
        try {
            // Validar que los datos requeridos estén presentes
            \Log::info('postMed - Validando datos de entrada');
            $validated = $request->validate([
                'lote' => 'required|string',
                'medidor' => 'required|string',
                'periodo' => 'required|integer',
                'fechaMedicion' => 'required|date',
                'vencimiento' => 'required|date',
                'valorMedido' => 'required|numeric',
                'inspector' => 'required|string',
                'foto' => 'nullable|string'
            ]);
            \Log::info('postMed - Validación exitosa', ['validated' => $validated]);
    
            $lote = $request->input('lote');
            $medidor = $request->input('medidor');
            $periodo = $request->input('periodo');
            $fechaAnt = $request->input('fechaAnt', null);
            $tomaAnterior = $request->input('tomaAnterior', null);
            $vencimiento = $request->input('vencimiento');
            $fechaMedicion = $request->input('fechaMedicion');
            $valorMedido = $request->input('valorMedido');
            $inspector = $request->input('inspector');
            $foto = $request->input('foto', 'Sin Foto');
    
            \Log::info('postMed - Datos extraídos', [
                'lote' => $lote,
                'medidor' => $medidor,
                'periodo' => $periodo,
                'fechaAnt' => $fechaAnt,
                'tomaAnterior' => $tomaAnterior,
                'vencimiento' => $vencimiento,
                'fechaMedicion' => $fechaMedicion,
                'valorMedido' => $valorMedido,
                'inspector' => $inspector,
                'foto' => $foto
            ]);
    
            // Obtener el índice de la última medición para este lote
            \Log::info('postMed - Obteniendo índice de mediciones para lote', ['lote' => $lote]);
            $indice = Medicion::where('lote', $lote)->count();
            \Log::info('postMed - Índice obtenido', ['indice' => $indice]);
    
            $tomaActual = (float)$valorMedido;
            $medidaAnt = 0;
            $consumo = 0;
    
            if($indice > 0) {
                \Log::info('postMed - Buscando última medición para lote', ['lote' => $lote]);
                $ultimaToma = Medicion::where('lote', $lote)
                                        ->orderBy('indice', 'desc')
                                        ->firstOrFail();
                $medidaAnt = (float)$ultimaToma->valormedido;
                $consumo = $tomaActual - $medidaAnt;
                \Log::info('postMed - Cálculo de consumo para medición existente', [
                    'medidaAnt' => $medidaAnt,
                    'tomaActual' => $tomaActual,
                    'consumo' => $consumo
                ]);
            } else {
                \Log::info('postMed - Primera medición para este lote');
                $medidaAnt = $tomaAnterior ? (float)$tomaAnterior : 0;
                $consumo = 0;
                \Log::info('postMed - Cálculo de consumo para primera medición', [
                    'medidaAnt' => $medidaAnt,
                    'tomaActual' => $tomaActual,
                    'consumo' => $consumo
                ]);
            }
    
            $indice++;
    
            \Log::info('postMed - Creando nueva medición en base de datos', [
                'lote' => $lote,
                'medidor' => $medidor,
                'periodo' => $periodo,
                'indice' => $indice,
                'fecha' => $fechaMedicion,
                'vencimiento' => $vencimiento,
                'tomaant' => $fechaAnt,
                'medidaant' => $medidaAnt,
                'valormedido' => $valorMedido,
                'consumo' => $consumo,
                'inspector' => $inspector,
                'foto' => $foto
            ]);
    
            // Crear la medición en la base de datos
            $medicion = Medicion::create([
                'lote' => $lote,
                'medidor' => $medidor,
                'periodo' => $periodo,
                'indice' => $indice,
                'fecha' => $fechaMedicion,
                'vencimiento' => $vencimiento,
                'tomaant' => $fechaAnt,
                'medidaant' => $medidaAnt,
                'valormedido' => $valorMedido,
                'consumo' => $consumo,
                'inspector' => $inspector,
                'foto' => $foto
            ]);
    
            \Log::info('postMed - Medición creada exitosamente', ['medicion_id' => $medicion->id]);
    
            return response()->json([
              'msg' => 'exito',
              'medicion' => $medicion,
              'success_message' => 'La medición se guardó correctamente'
          ], 200);
    
        } catch (\Exception $e) {
            \Log::error('postMed - Error al procesar la medición', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
    
            return response()->json([
                'msg' => 'error',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getGuardarFacturas(Request $request)      
    {
      $facturas = $request->datos;
     /* $codigoAysa = $request->factN;
      $diaDesde = $request->Desde;
      $diaHasta = $request->Hasta;
      $vencimientoAysa = $request->vAysa;
      $jsonenc = json_encode($facturas[0]);
      $jsondec = json_decode($jsonenc);*/
             
      $respuesta = response()->json(array('msg'=> $facturas), 200);
      //$respuesta = response()->json(array('msg'=> 'sjsjs'), 200);
      return $respuesta; 
    }

    public function postGuardarFacturas(Request $request)
    {
        $facturas = $request->datos;
        $codigoAysa = $request->factN;
        $diaDesde = $request->Desde;
        $diaHasta = $request->Hasta;
        $vencimientoAysa = $request->vAysa;
        $fijoVariable = $request->FijoVariable;
        $cantLotes = $request->CantLotes;
    
        $fijoProrateado = $fijoVariable / $cantLotes;
        $jsonenc = json_encode($facturas[0]);
        $jsondec = json_decode($jsonenc);
    
        $fechaObj1 = Carbon::parse($diaDesde);
        $fechaObj2 = Carbon::parse($diaHasta);
    
       // $diferenciaEnDias = $fechaObj2->diffInDays($fechaObj1);
       // Calcular la diferencia de días asegurando el orden correcto
        $diferenciaEnDias = $fechaObj1->diffInDays($fechaObj2, false); // El segundo parámetro "false" conserva el signo
    
        // Si la diferencia es negativa, invertir el orden para obtener un valor positivo
        if ($diferenciaEnDias < 0) {
            $diferenciaEnDias = $fechaObj2->diffInDays($fechaObj1);
        }
    
       
        
        foreach ($facturas as $clave => $valor) {
            $jsonenc = json_encode($valor);
            $jsondec = json_decode($jsonenc);
            $valxconsumo = $jsondec->valorxConsumo;
            $consumoxdia = round($jsondec->sumario / $diferenciaEnDias, 2);
    
            try {
                Factura::updateOrCreate(
                    ['codaysa' => $codigoAysa, 'lote' => $jsondec->lote],
                    [
                        'lote' => $jsondec->lote,
                        'medidor' => $jsondec->medidor,
                        'fdesde' => $diaDesde,
                        'fhasta' => $diaHasta,
                        'medant' => $jsondec->medidaant,
                        'ultmedida' => $jsondec->valormedido,
                        'consumo' => $jsondec->consumo,
                        'sumario' => $jsondec->sumario,
                        'valxconsumo' => 855,
                        'total' => $valxconsumo,
                        'fijovariable' => $fijoProrateado,
                        'fijovariabletotal' => $fijoVariable,
                        'periodo' => $diferenciaEnDias,
                        'conxdia' => $jsondec->sumario / $diferenciaEnDias,
                        'foto' => $jsondec->foto,
                        'codaysa' => $codigoAysa,
                        'venaysa' => $vencimientoAysa,
                    ]
                );
            } catch (\Throwable $th) {
                // Log the error
                Log::error('Error al guardar la factura: ' . $th->getMessage(), ['factura' => $valor]);
    
                $respuesta = response()->json(array('msg' => 'Error al guardar la factura: ' . $th->getMessage()), 500);
                return $respuesta;
            }
        }
    
        $respuesta = response()->json(array('msg' => 'Facturas guardadas correctamente'), 200);
        return $respuesta;
    }
   /* public function getMedicion(Request $request)
    {
      $indice = Medicion::where('lote', $request->lote)->count();
      $tomaActual = $request->valorMedido;
      if($indice > 0){ //si ya existe una medicion
     
        $ultimaToma = Medicion::where('lote', $request->lote)->orderBy('indice','desc')->firstOrFail();
        $medidaAnt = $ultimaToma->valormedido;
      
      }else{
       // primera medicion
        $medidaAnt = $request->tomaAnterior;       
      }
      $consumo =  (float)$tomaActual - (float)$medidaAnt;
  
      $indice++;
   
      Medicion::create([
        'lote' => $request->lote,
        'medidor' => $request->medidor,
        'periodo' => $request->periodo,
        'indice' => $indice,
        'fecha' => $request->fechaMedicion,
        'vencimiento' => $request->vencimiento,
        'tomaant' => $request->fechaAnt,
        'medidaant' => $medidaAnt,
        'valormedido' => $request->valorMedido,
        'consumo' =>$consumo,
        'inspector' => $request->inspector,
        'foto' => $request->foto,
      ]);
       
       return "exito";
    }*/

}