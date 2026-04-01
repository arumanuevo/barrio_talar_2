<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Medidor;
use Illuminate\Support\Facades\Hash;

class FixUserPasswords extends Command
{
    protected $signature = 'users:fix-passwords';
    protected $description = 'Fix user passwords to match medidores passwords';

    public function handle()
    {
        $users = User::all();

        foreach ($users as $user) {
            // Extraer el número de lote del email (ej: 1@gmail.com -> lote 1)
            $lote = explode('@', $user->email)[0];

            // Buscar el medidor correspondiente
            $medidor = Medidor::where('lote', $lote)->first();

            if ($medidor && $medidor->password) {
                // Actualizar la contraseña del usuario con el hash correcto
                $user->password = Hash::make($medidor->password);
                $user->save();

                $this->info("Usuario {$user->email} actualizado con contraseña correcta");
            } else {
                $this->warn("No se encontró medidor para el usuario {$user->email}");
            }
        }

        $this->info('Proceso completado!');
    }
}
