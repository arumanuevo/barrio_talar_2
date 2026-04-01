<?php
namespace App\Http\Controllers;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Lote;
use App\Models\SeccionBarrio;
use App\Models\User;
/*
Modificacion, este es un controlador que guarda los datos de lote asociados a los de los usuarios, a diferencia de antes que solo tenia
una talba para lote-medidor y otra tabla de registracion de usuarios, aqui puse todo junto

*/
class LoteController extends Controller
{
    
    /**
     * Create a new controller instance.
     *
     * @return void
     */
   /* public function __construct()
    {
        $this->middleware('auth');
    }
    */
    public function edit($id)
    {
        $lote = User::findOrFail($id); // Obtén el lote que deseas editar.
        $secciones = SeccionBarrio::pluck('nombreseccion', 'id');
        return view('editarLoteMedidor', compact('lote', 'secciones'));
    }

    public function destroy(Request $request)
    {
        $id = $request->input('id');
       // dd($request);
        //echo('sksksks');
        $lote = User::findOrFail($id);
        $lote->delete();
    
       // return redirect()->route('ListaCompletaLotes')->with('success', 'Usuario borrado con éxito');
        

    }

    public function update(Request $request, $id)
    {

        $medidor = $request->input('medidor');
        $email = $request->input('email');
        $ocupacion = $request->input('ocupacion');
        $name = $request->input('name');
        $telefono = $request->input('telefono');

        /*$request->validate([
            'lote' => 'required',
            'seccion' => 'required',
            'medidor' => 'required',
            'email' => 'required|email',
            'ocupacion' => 'required',
            'name' => 'required',
        ]);*/

        $lote = User::findOrFail($id);

        $lote->update($request->all());
        //dd($medidor);
        //dd($id);
        return redirect()->route('ListaCompletaLotes')->with('success', 'Lote actualizado con éxito');
    }
}

