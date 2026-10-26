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
        'porcentaje_abono',
    ];

    public function especialidad()
    {
        return $this->belongsTo(Especialidad::class, 'especialidad_id');
    }

    public function citas()
    {
        return $this->hasMany(Cita::class, 'servicio_id');
    }

    // Relación Muchos a Muchos con Usuarios (Especialistas).
    // Filtramos solo a los usuarios con rol 'trabajador'
    public function especialistas()
    {
        return $this->belongsToMany(User::class, 'servicio_user', 'servicio_id', 'user_id')
                    ->where('rol', 'trabajador');
    }
}