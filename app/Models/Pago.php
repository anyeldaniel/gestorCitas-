<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos'; // Conectando a tu tabla real

    protected $fillable = [
        'cita_id',
        'monto_anticipado',
        'metodo_pago',
        'banco_origen',
        'referencia',
        'telefono_pago',
        'comprobante_img',
        'estado_pago'
    ];

    // Relación: Un pago pertenece a una cita
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}