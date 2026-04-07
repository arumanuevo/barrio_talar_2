<?php

namespace App\Http\Controllers;

use App\Models\UsuarioParaLogin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UsuarioParaLoginController extends Controller
{
    public function showUploadForm()
    {
        return view('usuarios_para_login.upload');
    }

    public function processCSV(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        // Borrar todos los registros anteriores
        UsuarioParaLogin::truncate();

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();
        $fileContent = file_get_contents($filePath);

        // Convertir el contenido a UTF-8
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', mb_detect_encoding($fileContent, 'UTF-8, ISO-8859-1, Windows-1252', true));

        $fileHandle = fopen('php://temp', 'r+');
        fwrite($fileHandle, $fileContent);
        rewind($fileHandle);

        // Saltar la primera línea (encabezados)
        fgetcsv($fileHandle, 1000, ",");

        $importedRows = 0;
        $errors = [];

        while (($row = fgetcsv($fileHandle, 1000, ",")) !== FALSE) {
            try {
                // Generar una contraseña consistente: 2 palabras + número
                $passString = $this->generateConsistentPassword();

                UsuarioParaLogin::create([
                    'email' => $row[0] ?? null,
                    'lote' => $row[1] ?? null,
                    'medidor' => $row[2] ?? null,
                    'name' => mb_convert_encoding($row[3] ?? '', 'UTF-8', 'auto'),
                    'ocupacion' => mb_convert_encoding($row[4] ?? '', 'UTF-8', 'auto'),
                    'pass_string' => $passString,
                ]);
                $importedRows++;
            } catch (\Exception $e) {
                $errors[] = "Error en la fila: " . implode(", ", $row) . " - " . $e->getMessage();
            }
        }

        fclose($fileHandle);

        return redirect()->back()->with([
            'success' => "Se importaron $importedRows registros correctamente.",
            'errors' => $errors,
        ]);
    }

    public function previewCSV(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $filePath = $file->getRealPath();
        $fileContent = file_get_contents($filePath);

        // Convertir el contenido a UTF-8
        $fileContent = mb_convert_encoding($fileContent, 'UTF-8', mb_detect_encoding($fileContent, 'UTF-8, ISO-8859-1, Windows-1252', true));

        $lines = explode("\n", $fileContent);

        // Detectar delimitador
        $firstLine = trim($lines[0]);
        $delimiter = (strpos($firstLine, ';') !== false) ? ';' : ',';
        $headers = str_getcsv($firstLine, $delimiter);

        // Validar encabezados
        $expectedHeaders = ['EMAIL', 'LOTE', 'MEDIDOR', 'name', 'ocupacion'];
        $headersMatch = array_map('strtolower', $headers) === array_map('strtolower', $expectedHeaders);

        // Obtener todos los registros de datos
        $previewData = [];
        for ($i = 1; $i < count($lines); $i++) {
            if (trim($lines[$i]) === '') continue;
            $row = str_getcsv($lines[$i], $delimiter);
            if (count($row) === count($headers)) {
                $previewData[] = array_combine($headers, $row);
            }
        }

        return response()->json([
            'delimiter' => $delimiter,
            'headers' => $headers,
            'headers_match' => $headersMatch,
            'preview' => $previewData,
            'total_rows' => count($previewData),
        ]);
    }

    // Función para generar una contraseña consistente: 2 palabras + número
    private function generateConsistentPassword()
    {
        $spanishWords = [
            'casa', 'perro', 'gato', 'libro', 'mesa', 'silla', 'puerta', 'ventana', 'coche', 'arbol',
            'flor', 'agua', 'fuego', 'tierra', 'cielo', 'sol', 'luna', 'estrella', 'nube', 'montaña',
            'rio', 'mar', 'playa', 'bosque', 'campo', 'ciudad', 'pueblo', 'calle', 'plaza', 'parque',
            'jardin', 'puente', 'camino', 'sendero', 'caminata', 'correr', 'saltar', 'bailar', 'cantar',
            'hablar', 'comer', 'beber', 'dormir', 'soñar', 'reir', 'llorar', 'sonreir', 'abrazar', 'beso',
            'amigo', 'familia', 'amor', 'feliz', 'triste', 'enojado', 'sorpresa', 'miedo', 'calor', 'frio',
            'verano', 'invierno', 'otoño', 'primavera', 'dia', 'noche', 'mañana', 'tarde', 'amanecer', 'atardecer',
            'luz', 'sombra', 'color', 'blanco', 'negro', 'rojo', 'azul', 'verde', 'amarillo', 'naranja',
            'morado', 'rosa', 'gris', 'cafe', 'dulce', 'salado', 'amargo', 'acido', 'suave', 'duro',
            'blando', 'grande', 'pequeño', 'alto', 'bajo', 'largo', 'corto', 'ancho', 'estrecho', 'nuevo',
            'viejo', 'joven', 'rapido', 'lento', 'fuerte', 'debil', 'valiente', 'miedoso', 'honesto', 'mentiroso'
        ];

        // Seleccionar 2 palabras aleatorias
        $word1 = $this->removeAccents($spanishWords[array_rand($spanishWords)]);
        $word2 = $this->removeAccents($spanishWords[array_rand($spanishWords)]);

        // Generar un número aleatorio de 2 o 3 dígitos
        $number = rand(10, 999);

        // Combinar las palabras y el número
        return $word1 . $word2 . $number;
    }

    // Función para eliminar acentos y caracteres especiales
    private function removeAccents($string)
    {
        if (!preg_match('/[\x80-\xff]/', $string))
            return $string;

        $chars = [
            'á' => 'a', 'à' => 'a', 'â' => 'a', 'ä' => 'a', 'ã' => 'a',
            'Á' => 'A', 'À' => 'A', 'Â' => 'A', 'Ä' => 'A', 'Ã' => 'A',
            'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
            'É' => 'E', 'È' => 'E', 'Ê' => 'E', 'Ë' => 'E',
            'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
            'Í' => 'I', 'Ì' => 'I', 'Î' => 'I', 'Ï' => 'I',
            'ó' => 'o', 'ò' => 'o', 'ô' => 'o', 'ö' => 'o', 'õ' => 'o', 'ø' => 'o',
            'Ó' => 'O', 'Ò' => 'O', 'Ô' => 'O', 'Ö' => 'O', 'Õ' => 'O', 'Ø' => 'O',
            'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
            'Ú' => 'U', 'Ù' => 'U', 'Û' => 'U', 'Ü' => 'U',
            '/' => '-', ' ' => '-', ':' => '', '"' => '', "'" => '', '‘' => '', '’' => ''
        ];

        return strtr($string, $chars);
    }

    public function migrateToUsers()
{
    // Aumentar el tiempo de ejecución a 10 minutos (600 segundos)
    set_time_limit(600);

    // Limpiar tablas de Spatie (opcional, solo si quieres reiniciar roles/permisos)
    \DB::table('model_has_roles')->truncate();
    \DB::table('model_has_permissions')->truncate();

    // Obtener todos los usuarios de usuarios_para_login (con chunk para evitar sobrecargar memoria)
    $totalUsuarios = UsuarioParaLogin::count();
    $chunkSize = 100; // Procesar de a 100 registros por vez
    $migratedCount = 0;

    // Obtener el rol 'user' (role_id = 2)
    $role = Role::find(2); // Buscar por ID en lugar de por nombre
    if (!$role) {
        return redirect()->back()->with('error', 'El rol "user" no existe. Debes crearlo primero.');
    }

    // Obtener el permiso con ID 1
    $permission = Permission::find(1);
    if (!$permission) {
        return redirect()->back()->with('error', 'El permiso con ID 1 no existe. Debes crearlo primero.');
    }

    // Limpiar la tabla users (opcional, solo si quieres reiniciar)
    // User::truncate();

    // Procesar los usuarios en chunks para evitar sobrecargar la memoria
    UsuarioParaLogin::chunk($chunkSize, function ($usuarios) use ($role, $permission, &$migratedCount) {
        foreach ($usuarios as $usuario) {
            $user = User::create([
                'name' => $usuario->name,
                'email' => $usuario->email,
                'telefono' => '', // Puedes asignar un valor por defecto o dejarlo vacío
                'lote' => $usuario->lote,
                'ocupacion' => $usuario->ocupacion,
                'medidor' => $usuario->medidor,
                'seccion' => '', // Puedes asignar un valor por defecto o dejarlo vacío
                'password' => Hash::make($usuario->pass_string), // Hashear la pass_string
                'verificado' => 'NO',
                'rol' => 'invitado',
            ]);

            // Asignar rol y permiso
            $user->assignRole($role);
            $user->givePermissionTo($permission);

            $migratedCount++;
        }
    });

    return redirect()->back()->with('success', 'Se migraron ' . $migratedCount . ' usuarios correctamente.');
}

}