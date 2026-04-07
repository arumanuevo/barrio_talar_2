<?php

namespace App\Http\Controllers;

use App\Models\MedicionProvisoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Models\User;

class MedicionProvisoriaController extends Controller
{
    public function index()
{
    $lotes = User::where('lote', '!=', '0')
                ->orderBy('id', 'asc')
                ->get(['id', 'lote', 'medidor']);

    // Obtener los lotes que ya tienen mediciones
    $lotesConMedicion = MedicionProvisoria::pluck('lote')->toArray();

    // Agregar el atributo tiene_medicion a cada lote
    $lotes = $lotes->map(function($lote) use ($lotesConMedicion) {
        $lote->tiene_medicion = in_array($lote->lote, $lotesConMedicion);
        return $lote;
    });

    return view('mediciones_provisorias.index', compact('lotes'));
}


    public function store(Request $request)
{
    $request->validate([
        'lote' => 'required|string|max:50',
        'medidor' => 'nullable|string|max:50',
        'consumo' => 'required|numeric|min:0',
        'fecha_medicion' => 'required|date',
        'foto' => 'nullable|string',
    ]);

    $fotoPath = null;
    if ($request->foto && $request->foto != 'N/A') {
        $fotoPath = $this->guardarFotoProvisoria($request->foto, $request->lote, $request->fecha_medicion);
    }

    MedicionProvisoria::create([
        'lote' => $request->lote,
        'medidor' => $request->medidor,
        'consumo' => $request->consumo,
        'foto' => $fotoPath,
        'fecha_medicion' => $request->fecha_medicion,
    ]);

    return redirect()->back()->with('success', 'Medición provisoria guardada correctamente.');
}

public function guardarFotoProvisoria($imageData, $lote, $fechaMedicion)
{
    // Remover el prefijo de la imagen base64
    $imageData = str_replace('data:image/png;base64,', '', $imageData);
    $imageData = base64_decode($imageData);

    // Formatear la fecha para el nombre del archivo
    $fechaFormateada = \Carbon\Carbon::parse($fechaMedicion)->format('Ymd');

    // Generar el nombre del archivo según el formato especificado
    $filename = 'talar2_' . $lote . '_' . $fechaFormateada . '.png';
    $path = 'images/' . $filename;

    // Guardar la imagen en el directorio public/images
    file_put_contents(public_path($path), $imageData);

    return $path;
}

    public function obtenerMedidor($lote)
    {
        $user = User::where('lote', $lote)->first();

        if ($user) {
            return response()->json(['medidor' => $user->medidor]);
        } else {
            return response()->json(['medidor' => ''], 404);
        }
    }

    public function indexListado()
    {
        $mediciones = MedicionProvisoria::with('user')->orderBy('fecha_medicion', 'desc')->get();

        return view('mediciones_provisorias.listado', compact('mediciones'));
    }
}
