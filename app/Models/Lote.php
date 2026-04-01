<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lote extends Model
{
    public $timestamps = false;
    protected $table = 'lote_medidor';
    protected $fillable = ['lote','seccion', 'medidor', 'nombre', 'ocupacion','email','tel','env_aut','seccion'];
}
