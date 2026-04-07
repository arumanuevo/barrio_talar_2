<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsuarioParaLogin extends Model
{
    use HasFactory;

    protected $table = 'usuarios_para_login';

    protected $fillable = [
        'email',
        'lote',
        'medidor',
        'name',
        'ocupacion',
        'pass_string', // Agrega esta línea
    ];
}