<?php

// app/Http/Controllers/PasswordController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ContraseniasGeneradas;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContraseniasGeneradasExport;
use App\Models\SeccionBarrio;
use App\Http\Controllers\Auth;
use App\Models\User;

class PasswordController extends Controller
{
   /* public function __construct()
    {
        $this->middleware('auth');
    }*/

    public function index()
    {
        
        $contraseñasGeneradas = ContraseniasGeneradas::latest()->get();
        //$secciones = SeccionBarrio::pluck('nombreseccion', 'id')->prepend('Defina seccion', '');
        
        return view('generadorPassword', compact('contraseñasGeneradas'));
    }



    public function generatePasswords(Request $request)
    {
        $cantidad = $request->input('cantidad');
    
        if ($cantidad > 0) {
            $lastLote = ContraseniasGeneradas::max('lote') ?? 0;
           

            $contraseñas = [];

            for ($i = 1; $i <= $cantidad; $i++) {
                $lote = $lastLote + $i; // Incrementa lote correctamente
                $password = $this->generateRandomPassword();
                ContraseniasGeneradas::create([
                    'lote' => $lote,
                    'pass' => $password,
                ]);
            }
            $lastLote += $cantidad; // Actualiza lastLote después de la creación de contraseñas
        } else {
            $contraseñas = [];
        }
    
        $contraseñasGeneradas = ContraseniasGeneradas::latest()->get();
    
        return view('generadorPassword', compact('contraseñasGeneradas', 'contraseñas'));
    }
    

    private function generateRandomPassword($length = 12)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $password;
    }

    public function exportToExcel()
    {
        return Excel::download(new ContraseniasGeneradasExport, 'contraseñas_generadas.xlsx');
    }
}

