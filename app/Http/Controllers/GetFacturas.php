<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Lote;
use App\Models\Medicion;
use App\Models\Factura;
use Carbon\Carbon;
class GetFacturas extends Controller
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
    public function getFacturas(Request $request ) //para usuario de lote
    {
        $userId = Auth::user()->id;
        $lote = Auth::user()->lote;
       
        
        $facturasLote = Factura::where('lote', $lote)
                        ->get();
       // $mediciones = Medicion::all();
        //$mediciones = Medicion::paginate(15);
        return view('infoFacturas')->with('facturasLote',$facturasLote);
      //  return $facturasLote;
       // echo($facturasLote);
        //echo $userId;
      //  $lotes = Lote::all();
      //  $inspectores = Inspector::all();
      ////// return view('infoFacturas')->with('facturasLote',$facturasLote);
       // return view('medir')->with(compact('lotes','inspectores'));
        //return view('infoFacturas')->with(compact('lotes','inspectores'));
       // return $facturasLote;
      //  return view('infoFacturas');
      // return view('ajax-request')->with(compact('lotes','inspectores'));
       // return view('ListadoVentas');
    }

    public function getTodasFacturas(Request $request)
    {
        $order = $request->input('order', 'asc');
        $sort = $request->input('sort', 'lote');
        $search = $request->input('search');
    
        // Consulta de base de datos con ordenamiento y búsqueda
        $query = Factura::orderBy($sort, $order);
  
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('codaysa', 'LIKE', "%$search%")
                  ->orWhere('venaysa', 'LIKE', "%$search%")
                  ->orWhere('lote', 'LIKE', "%$search%")
                  ->orWhere('medidor', 'LIKE', "%$search%")
                  ->orWhere('fdesde', 'LIKE', "%$search%")
                  ->orWhere('fhasta', 'LIKE', "%$search%")
                  ->orWhere('total', 'LIKE', "%$search%")
                  ->orWhere('sumario', 'LIKE', "%$search%")
                  ->orWhere('conxdia', 'LIKE', "%$search%");
            });
        }
    
        $facturasTodas = $query->paginate(12);

        return view('listaCompletaFacturas', compact('facturasTodas', 'order', 'search','sort'));
    }
    

   /* public function getFacturasGraf(Request $request )
    {
        $userId = Auth::user()->id;
        $lote = Auth::user()->lote;
        $facturasLote = Factura::where('lote', $lote)->get();
        return $facturasLote;
    }*/
   /* public function getFacturasGrafVista(Request $request )
    {
        $userId = Auth::user()->id;
        $lote = Auth::user()->lote;
        $facturasLote = Factura::where('lote', $lote)->get();
       
       // return view('grafConsumos')->with('facturasLote',$facturasLote);
       // return view('grafConsumos');
        return view('grafConsumos')->with('facturasLote',$facturasLote);
    }*/

    public function getFacturasGrafVista(Request $request)
{
    $userId = Auth::user()->id;
    $lote = Auth::user()->lote;
    $facturasLote = Factura::where('lote', $lote)->get();
    
    $data = [];
    foreach ($facturasLote as $factura) {
        $data['labels'][] = Carbon::parse($factura->fdesde)->format('Y-m-d') . ' - ' . Carbon::parse($factura->fhasta)->format('Y-m-d');
        $data['sumarios'][] = $factura->sumario;
    }
    
    return view('grafConsumos')->with('data', json_encode($data));
}


   
}
