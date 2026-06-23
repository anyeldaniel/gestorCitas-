<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    // El array $fillable actúa como un escudo. 
    // Solo las columnas que estén aquí podrán ser llenadas desde un formulario.
    protected $fillable = [
        'nombre_servicio',
        'descripcion',
        'imagen',
        'precio',
        'duracion_minutos',
        'especialidad_id',
        'porcentaje_agendado'
    ];
}