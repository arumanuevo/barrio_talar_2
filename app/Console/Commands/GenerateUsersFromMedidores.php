<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Medidor;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class GenerateUsersFromMedidores extends Command
{
    protected $signature = 'users:generate-from-medidores';
    protected $description = 'Generate users from medidores data';

    public function handle()
    {
        $medidores = Medidor::all();
        $userRole = Role::where('name', 'user')->first();

        if (!$userRole) {
            $this->error('El rol "user" no existe. Creando el rol "user"...');
            $userRole = Role::create(['name' => 'user']);
        }

        foreach ($medidores as $medidor) {
            $email = $medidor->lote . '@gmail.com';
            $password = Str::random(8);

            // Verificar si ya existe un usuario con este correo electrónico
            $existingUser = User::where('email', $email)->first();

            if (!$existingUser) {
                $user = User::create([
                    'name' => 'Ingrese Nombre',
                    'email' => $email,
                    'telefono' => '0000',
                    'lote' => $medidor->lote,
                    'ocupacion' => 'casa',
                    'medidor' => $medidor->numero_medidor,
                    'seccion' => null,
                    'password' => Hash::make($password), // Usamos Hash::make en lugar de bcrypt
                    'verificado' => false,
                    'rol' => 'user',
                ]);

                $user->assignRole($userRole);

                // Actualizar el medidor con la contraseña generada
                $medidor->update(['password' => $password]);

                $this->info("Usuario creado para el lote: {$medidor->lote} con email: {$email} y contraseña: {$password}");
            } else {
                $this->warn("El usuario para el lote: {$medidor->lote} ya existe. Saltando...");
            }
        }

        $this->info('Proceso completado!');
    }
}
