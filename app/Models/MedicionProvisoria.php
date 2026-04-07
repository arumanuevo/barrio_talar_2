<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MedicionProvisoria extends Model
{
    use HasFactory;

    protected $table = 'mediciones_provisorias';

    protected $fillable = [
        'lote',
        'medidor',
        'consumo',
        'foto',
        'fecha_medicion',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'lote', 'lote');
    }
}