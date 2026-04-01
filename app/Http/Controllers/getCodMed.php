<?php

namespace App\Http\Controllers;
use App\Models\User;

use Illuminate\Http\Request;

class getCodMed extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
    }

    public function getCodMed($lote){
        
       /* $usuario = User::where('lote', $lote)->first();

        if ($usuario) {
            return response()->json(['medidor' => $usuario->medidor]);
        } else {
            return response()->json(['error' => 'No se encontró ningún usuario con ese lote'], 404);
        }*/

        if (Auth::check()) {
            // Obtener el usuario autenticado
            $usuario = Auth::user();
    
            // Obtener el token de acceso del usuario
            $token = $usuario->createToken('TokenName')->plainTextToken;
    
            // Obtener el medidor asociado al lote
            $medidor = User::where('lote', $lote)->value('medidor');
    
            if ($medidor) {
                return view('medir', ['token' => $token, 'medidor' => $medidor]);
            } else {
                return response()->json(['error' => 'No se encontró ningún medidor asociado a ese lote'], 404);
            }
        } else {
            // El usuario no está autenticado, redirigir a la página de inicio de sesión
            return redirect()->route('login');
        }
    }
}
