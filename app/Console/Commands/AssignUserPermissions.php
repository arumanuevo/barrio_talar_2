<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AssignUserPermissions extends Command
{
    protected $signature = 'users:assign-permissions';
    protected $description = 'Assign permissions to users based on their roles';

    public function handle()
    {
        // Crear el permiso 'usuario' si no existe
        $usuarioPermission = Permission::firstOrCreate(['name' => 'usuario']);

        // Obtener todos los usuarios con rol 'user'
        $users = User::role('user')->get();

        $this->info("Encontrados {$users->count()} usuarios con rol 'user'");

        foreach ($users as $user) {
            // Asignar el permiso 'usuario' a cada usuario con rol 'user'
            if (!$user->hasPermissionTo('usuario')) {
                $user->givePermissionTo($usuarioPermission);
                $this->info("Permiso 'usuario' asignado a {$user->email}");
            } else {
                $this->warn("El usuario {$user->email} ya tiene el permiso 'usuario'");
            }
        }

        // También asignar el permiso al rol 'user' para futuros usuarios
        $userRole = Role::where('name', 'user')->first();
        if ($userRole && !$userRole->hasPermissionTo('usuario')) {
            $userRole->givePermissionTo($usuarioPermission);
            $this->info("Permiso 'usuario' asignado al rol 'user'");
        }

        $this->info('Proceso completado!');
    }
}
