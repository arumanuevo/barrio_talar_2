<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lote;
use App\Models\Medicion;
class GuardarMedicion extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
       // $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function postMedicion(Request $request) //no se usa
    {
      
       /* $medida = Medicion::firstOrNew(['lote' => $request->lote]);
 
        $medida->medidor = $request->medidor;
        $medida->valormedido = $request->valorMedido;
        $medida->fecha = $request->fechaMedicion;
        $medida->valormedido = $request->valorMedido;
        $medida->inspector = $request->inspector;
        $medida->foto = 'path a foto';
        
        $medida->save();*/

      /* $medida = Medicion::firstOrNew([
            ['lote' => $request->lote],
           [ 'medidor' => $request->medidor,
            'fecha' => $request->fechaMedicion,
            'valormedido' => $request->valorMedido,
            'inspector' => $request->inspector,
            'foto' => 'path a foto',]
            ]
        );
    
        $medida->save();*/
    
       Medicion::create([
         'lote' => $request->lote,
         'medidor' => $request->medidor,
         'periodo' => $request->periodo,
         'fecha' => $request->fechaMedicion,
         'vencimiento' => $request->vencimiento,
         'tomaant' => $request->fechaAnt,
         'valormedido' => $request->valorMedido,
         'inspector' => $request->inspector,
         'foto' => 'path a foto',
         ]);
         
        return $request;
       // $lotes = Lote::all();
       // return view('medir')->with('lotes',$lotes);
       // return view('ListadoVentas');
    }
    
    public function getMedicion(Request $request)
    {
      $indice = Medicion::where('lote', $request->lote)->count();
      $tomaActual = $request->valorMedido;
      if($indice > 0){ //si ya existe una medicion
      //  $medidaAnt = Medicion::where('lote', $request->lote)->latest()->take(1)->get(); //ultimas dos tomas
       // $tomaActual = $request->valorMedido;
        $ultimaToma = Medicion::where('lote', $request->lote)->orderBy('indice','desc')->firstOrFail();
        $medidaAnt = $ultimaToma->valormedido;
       // $consumo = (float)$tomaActual - (float)$medidaAnt;
        //echo($medidaAnt);
      }else{
       // primera medicion
        $medidaAnt = $request->tomaAnterior;       
      }
      $consumo =  (float)$tomaActual - (float)$medidaAnt;
    /*  $medidaAnt = Medicion::where('lote', $request->lote)->orderBy('indice','desc')->firstOrFail()->get();
      $mediciones = Medicion::where('lote', $request->lote);
      $cantidadMediciones = $mediciones->count();
      if ($cantidadMediciones <> null){
        dd($cantidadMediciones);
      }else{
        dd('sjsjsjs');
      }*/
     // dd($indice);
      
      $indice++;
     // $medidaAnt = Medicion::where('lote', $request->lote)->latest()->take(1)->get();
     // echo $medidaAnt;
     // Dogs::latest()->take(5)->get();
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
        
       return 'exito';
    }
}
