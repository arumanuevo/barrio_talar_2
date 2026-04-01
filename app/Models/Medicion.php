<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medicion extends Model
{
    //use HasFactory;
   
    protected $table = 'mediciones';
    protected $fillable = ['lote','medidor','periodo','indice','fecha',
    'vencimiento','tomaant','medidaant','valormedido','consumo',
    'inspector','foto','pagado'];
}
