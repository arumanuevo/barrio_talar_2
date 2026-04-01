<?php

namespace App\Http\Controllers;

use App\Models\Medidor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class MedidorController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
     
    }

    public function index()
    {
        $medidores = Medidor::with(['user' => function($query) {
                $query->select('id', 'lote', 'email');
            }])
            ->select('id', 'lote', 'numero_medidor', 'password')
            ->orderBy('lote')
            ->get();

        return view('medidores.index', compact('medidores'));
    }

    public function create()
    {
        return view('medidores.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'lote' => 'required|string|max:11|unique:medidores',
            'email' => 'required|email|unique:users',
            'numero_medidor' => 'required|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        // Crear el medidor
        $medidor = Medidor::create([
            'lote' => $request->lote,
            'numero_medidor' => $request->numero_medidor,
            'password' => $request->password,
        ]);

        // Crear el usuario
        $user = User::create([
            'name' => 'Ingrese Nombre',
            'email' => $request->email,
            'telefono' => '0000',
            'lote' => $request->lote,
            'ocupacion' => 'casa',
            'medidor' => $request->numero_medidor,
            'seccion' => null,
            'password' => Hash::make($request->password),
            'verificado' => false,
            'rol' => 'user',
        ]);

        // Asignar rol 'user'
        $userRole = Role::where('name', 'user')->first();
        if ($userRole) {
            $user->assignRole($userRole);
        }

        // Asignar permiso 'usuario'
        $usuarioPermission = \Spatie\Permission\Models\Permission::where('name', 'usuario')->first();
        if ($usuarioPermission) {
            $user->givePermissionTo($usuarioPermission);
        }

        return redirect()->route('medidores.index')
            ->with('success', 'Medidor y usuario creados correctamente');
    }

    public function edit($id)
    {
        $medidor = Medidor::with(['user' => function($query) {
                $query->select('id', 'lote', 'email');
            }])
            ->findOrFail($id);

        return view('medidores.edit', compact('medidor'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($request->user_id, 'id')
            ],
            'numero_medidor' => 'required|string|max:20',
            'password' => 'required|string|min:6',
        ]);

        // Actualizar el medidor
        $medidor = Medidor::findOrFail($id);
        $medidor->numero_medidor = $request->numero_medidor;
        $medidor->password = $request->password;
        $medidor->save();

        // Actualizar el usuario si existe
        if ($request->user_id) {
            $user = User::find($request->user_id);
            if ($user) {
                $user->email = $request->email;
                $user->password = Hash::make($request->password);
                $user->save();
            }
        }

        return redirect()->route('medidores.index')
            ->with('success', 'Medidor y usuario actualizados correctamente');
    }

    
}
