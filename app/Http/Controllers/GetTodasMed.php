<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lote;
use App\Models\Medicion;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\StreamedResponse;

class GetTodasMed extends Controller
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

        public function getTodasMedVista(Request $request)
        {
           

            return view('listaMediciones');

        }

        public function getTodasMed()
        {
            $mediciones = Medicion::all()->sortBy(function($medicion) {
                // Extraer el número del lote
                preg_match('/(\d+)/', $medicion->lote, $matches);
                $numero = isset($matches[0]) ? (int)$matches[0] : 0;
        
                // Si el lote tiene el sufijo 'bis', añadir un valor decimal para ordenarlo correctamente
                if (strpos($medicion->lote, 'bis') !== false) {
                    $numero += 0.5;
                }
        
                return $numero;
            })->values();
        
            // Estructura los datos para que DataTables pueda procesarlos
            return response()->json([
                'data' => $mediciones // DataTables espera que los datos estén bajo la clave 'data'
            ]);
        }
        

        public function editarMedicion(Request $request) //vista blade
        {
            
            $id = $request->query('id');
            
            $medicion = Medicion::findOrFail($id);
            return view('editarMedicion', compact('medicion'));
        }


      /*  public function actualizarMedicion(Request $request)
        {
            $id = $request->input('id');
           
            $medicion = Medicion::findOrFail($id);

            // Actualiza los campos de la medición según los datos enviados por el formulario
            $data = $request->all();

            // Convierte las fechas del formato "dd-mm-aaaa" al formato "aaaa-mm-dd"
            $data['fecha'] = Carbon::createFromFormat('d-m-Y', $data['fecha'])->format('Y-m-d');
            $data['vencimiento'] = Carbon::createFromFormat('d-m-Y', $data['vencimiento'])->format('Y-m-d');
            $data['tomaant'] = Carbon::createFromFormat('d-m-Y', $data['tomaant'])->format('Y-m-d');

            $medicion->fill($data);
            $medicion->save();

            return redirect()->route('getTodasMed')->with('status', 'Medición actualizada con éxito');
        }*/
        public function actualizarMedicion(Request $request, $id)
        {
           
           
            $medicion = Medicion::findOrFail($id);

            // Actualiza los campos de la medición según los datos enviados por el formulario
            $data = $request->all();
           
            // Convierte las fechas del formato "dd-mm-aaaa" al formato "aaaa-mm-dd"
            $data['fecha'] = Carbon::createFromFormat('d-m-Y', $data['fecha'])->format('Y-m-d');
            $data['vencimiento'] = Carbon::createFromFormat('d-m-Y', $data['vencimiento'])->format('Y-m-d');
            $data['tomaant'] = Carbon::createFromFormat('d-m-Y', $data['tomaant'])->format('Y-m-d');
            $tomaActual = $request->valorMedido;
            $data['consumo'] =  $data['valormedido'] -  $data['medidaant'];

            $medicion->fill($data);
            $medicion->save();

            return redirect()->route('getTodasMedVista')->with('status', 'Medición actualizada con éxito');
        }

        public function exportarMediciones()
        {
            $response = new StreamedResponse(function() {
                // Obtener los datos que deseas exportar
                $mediciones = Medicion::all(); // Aquí puedes filtrar según los datos que quieras exportar

                // Crear el archivo CSV
                $handle = fopen('php://output', 'w');
                fputcsv($handle, [
                    'Lote', 'Medidor', 'Periodo', 'Fecha', 'Vencimiento', 'Toma Ant', 
                    'Medida Ant', 'Valor Medido', 'Consumo', 'Inspector'
                ], ';'); // Cambiado el delimitador a punto y coma

                // Iterar sobre los datos para incluirlos en el CSV
                foreach ($mediciones as $medicion) {
                    fputcsv($handle, [
                        $medicion->lote,
                        $medicion->medidor,
                        $medicion->periodo,
                        $medicion->fecha,
                        $medicion->vencimiento,
                        $medicion->tomaant,
                        $medicion->medidaant,
                        $medicion->valormedido,
                        $medicion->consumo,
                        $medicion->inspector
                    ]);
                }

                fclose($handle);
            });

            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', 'attachment; filename="mediciones.csv"');

            return $response;
        }

}
