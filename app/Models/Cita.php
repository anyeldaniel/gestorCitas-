<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    // Definimos los campos que se pueden asignar masivamente.
    protected $fillable = [
        'cliente_id',
        'trabajador_id',
        'servicio_id',
        'fecha',
        'hora',
        'estado'
    ];
}