<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
Use \Carbon\Carbon;

class Factura extends Model
{
    //use HasFactory;
    protected $table = 'facturaciones';
    protected $casts = [
        'venaysa' => 'datetime',
        'fdesde' => 'datetime',
        'fhasta' => 'datetime',
    ];
    
    protected $fillable = ['lote','medidor','fdesde','fhasta', 'periodo',
    'medant','ultmedida','consumo','sumario','conxdia','total', 'fijovariable', 'fijovariabletotal',
    'foto','codaysa','venaysa'];
}
