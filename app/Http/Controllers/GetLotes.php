<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lote;
class GetLotes extends Controller
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
       /* public function getLotes(Request $request )
        {
            $lotes = Lote::all();
           // $lotes = Lote::where('medidor','<>', 'N/A')->get();
            $respuesta = response()->json(array('msg'=> $lotes), 200);
            return $respuesta;
        }*/

        /**
         * Show the application dashboard.
         *
         * @return \Illuminate\Contracts\Support\Renderable
         */
        public function index(Request $request )
        {
    
            return view('listaCompleta');
           // return view('ListadoVentas');
        }
}
