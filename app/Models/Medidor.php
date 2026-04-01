<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Medidor extends Model
{
    use HasFactory;

    protected $table = 'medidores';
    public $timestamps = false;

    protected $fillable = [
        'lote',
        'numero_medidor',
        'password',
    ];

    /**
     * Relación con el modelo User.
     * Un medidor pertenece a un usuario (propietario del lote).
     */
    public function user()
    {
        return $this->hasOne(User::class, 'lote', 'lote');
        
    }
}


