<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    // Definimos los campos que se pueden asignar masivamente.
    protected $fillable = [
        'cliente_id',
        'trabajador_id',
        'servicio_id',
        'cabina_id',
        'fecha',
        'hora',
        'monto_total',
        'estado'
    ];

    // Relación con el cliente (Tabla 'usuarios')
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    // Relación con el trabajador/especialista (Tabla 'usuarios')
    public function trabajador()
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }

    // Relación con el servicio (Tabla 'servicios')
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    // Relación con los pagos de la cita
    public function pagos()
    {
        return $this->hasMany(Pago::class, 'cita_id');
    }
}