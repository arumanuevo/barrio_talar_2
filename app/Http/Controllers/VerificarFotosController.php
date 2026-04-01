<?php

namespace App\Http\Controllers;

use App\Models\Medicion;
use Illuminate\Support\Facades\File;

class VerificarFotosController extends Controller
{
    public function index()
    {
        // Obtener y normalizar nombres de fotos de la DB
        $fotosEnDB = Medicion::whereNotNull('foto')
            ->where('foto', '!=', 'Sin foto')
            ->select(['id', 'foto'])
            ->cursor()
            ->pluck('foto')
            ->map(function ($nombre) {
                // Eliminar extensión si existe y agregar .png
                $base = pathinfo($nombre, PATHINFO_FILENAME);
                return $base . '.png';
            })
            ->unique()
            ->values()
            ->toArray();

        // Obtener nombres de archivos del directorio
        $fotosEnDisco = collect(File::files(public_path('images')))
            ->map(fn($file) => $file->getFilename())
            ->toArray();

        // Calcular diferencias
        $faltantes = array_diff($fotosEnDB, $fotosEnDisco);

        return view('admin.fotos-verificacion', [
            'totalRegistros' => count($fotosEnDB),
            'faltantes' => $faltantes,
            'contadorFaltantes' => count($faltantes)
        ]);
    }
}