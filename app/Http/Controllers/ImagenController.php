<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class ImagenController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function mostrarFormularioCarga()
    {
        return view('cargar_imagenes');
    }

    public function guardarImagenes(Request $request)
    {
        try {
            // Verificar si es una solicitud AJAX
            $isAjax = $request->ajax() || $request->wantsJson();

            // Validar que se hayan enviado archivos
            if (!$request->hasFile('imagenes')) {
                if ($isAjax) {
                    return response()->json([
                        'error' => 'No se han seleccionado imágenes para subir.'
                    ], 400);
                }
                return redirect()->back()->with('error', 'No se han seleccionado imágenes para subir.');
            }

            $tamañoMaximo = 2048; // 2MB
            $images = $request->file('imagenes');
            $resultados = [];

            foreach ($images as $imagen) {
                $nombreOriginal = $imagen->getClientOriginalName();
                $tamaño = $imagen->getSize();
                $tipo = strtolower($imagen->getClientOriginalExtension());

                $resultado = [
                    'nombre' => $nombreOriginal,
                    'estado' => 'éxito',
                    'mensaje' => 'Imagen subida correctamente'
                ];

                // Verificar tipo de archivo
                if ($tipo !== 'png') {
                    $resultado['estado'] = 'error';
                    $resultado['mensaje'] = 'Solo se permiten archivos PNG.';
                    $resultados[] = $resultado;
                    continue;
                }

                // Verificar tamaño
                if ($tamaño > $tamañoMaximo * 1024) {
                    $resultado['estado'] = 'error';
                    $resultado['mensaje'] = 'El tamaño de la imagen supera el límite de 2MB.';
                    $resultados[] = $resultado;
                    continue;
                }

                // Guardar la imagen
                try {
                    $nombreNormalizado = Str::lower($nombreOriginal);
                    $imagen->move(public_path('images'), $nombreNormalizado);
                    $resultado['nombre'] = $nombreNormalizado;
                    $resultados[] = $resultado;
                } catch (\Exception $e) {
                    $resultado['estado'] = 'error';
                    $resultado['mensaje'] = 'Error al guardar la imagen: ' . $e->getMessage();
                    $resultados[] = $resultado;
                }
            }

            if ($isAjax) {
                return response()->json(['resultados' => $resultados]);
            }

            return redirect()->back()->with('resultados', $resultados);

        } catch (\Exception $e) {
            Log::error('Error al subir imágenes: ' . $e->getMessage());

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Error en el servidor: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error en el servidor: ' . $e->getMessage());
        }
    }

    public function subirFotoMedicion(Request $request)
    {
        try {
            // Validar que se haya enviado una foto
            if (!$request->hasFile('foto')) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'No se ha enviado ninguna foto'
                ], 400);
            }

            $tamañoMaximo = 2048; // 2MB
            $foto = $request->file('foto');
            $tipo = strtolower($foto->getClientOriginalExtension());
            $nombreOriginal = $foto->getClientOriginalName();

            // Verificar tipo de archivo
            if ($tipo !== 'png') {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'Solo se permiten archivos PNG.'
                ], 400);
            }

            // Verificar tamaño
            if ($foto->getSize() > $tamañoMaximo * 1024) {
                return response()->json([
                    'estado' => 'error',
                    'mensaje' => 'El tamaño de la imagen supera el límite de 2MB.'
                ], 400);
            }

            // Guardar la imagen
            $nombreNormalizado = Str::lower($nombreOriginal);
            $foto->move(public_path('images'), $nombreNormalizado);

            return response()->json([
                'estado' => 'éxito',
                'mensaje' => 'Imagen subida correctamente',
                'nombre' => $nombreNormalizado
            ]);

        } catch (\Exception $e) {
            Log::error('Error al subir foto de medición: ' . $e->getMessage());
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'Error en el servidor: ' . $e->getMessage()
            ], 500);
        }
    }
    
}
