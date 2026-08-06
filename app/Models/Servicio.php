<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Servicio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre_servicio',
        'descripcion',
        'imagen',
        'precio',
        'duracion_minutos',
        'especialidad_id',
        'porcentaje_agendado'
    ];

    // Relación Muchos a Muchos con Usuarios (Especialistas).
    public function especialistas()
    {
        return $this->belongsToMany(User::class, 'servicio_user', 'servicio_id', 'user_id');
    }

}