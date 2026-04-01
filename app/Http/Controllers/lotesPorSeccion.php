<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lote;
use App\Models\User;

class lotesPorSeccion extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
   /* public function __construct()
    {
        $this->middleware('auth');
    }*/


    public function index(Request $request)
    {
        
       // $seccionUsuario = auth()->user()->seccion;
     
       /* $seccion = $request->input('seccion');
        $lotesSeccion = User::where('seccion', '=', $seccion)->get();


        $respuesta = response()->json(array('msg'=> $lotesSeccion), 200);
  
        return $respuesta; 

        $seccion = $request->numSeccion; */

        /*if (auth()->check()) {
            // Si está autenticado, utiliza la sección del usuario
            $seccion = auth()->user()->seccion;
        } else {
            // Si no está autenticado, utiliza la sección del request (si está presente)
            $seccion = $request->input('seccion');
        }*/
     
        $seccion = $request->input('seccion');
        $lotes = User::where('seccion', '=', $seccion)->get();
        $definaLote = new \stdClass();
        $definaLote->id = null;  // O algún valor único que represente "Defina Lote"
        $definaLote->lote = 'N/A';
        $lotes->prepend($definaLote);
        // $lotes = Lote::where('medidor','<>', 'N/A')->get();
        
        return response()->json($lotes);
    }
}
