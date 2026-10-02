<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    // Nombre real de tu tabla en XAMPP
    protected $table = 'citas';

    // Columnas que Laravel puede llenar masivamente
    protected $fillable = [
        'cliente_id',
        'trabajador_id',
        'servicio_id',
        'cabina_id',
        'monto_total',
        'fecha',
        'hora',
        'estado',
    ];

    // Conversión de tipos automática
    protected $casts = [
        'fecha'       => 'date',
        'monto_total' => 'decimal:2',
        'created_at'  => 'datetime',
        'updated_at'  => 'datetime',
    ];

    /* ==========================================================
       RELACIONES
       ========================================================== */

    /**
     * Cliente que reservó la cita (usuario)
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    /**
     * Terapeuta/trabajador que atenderá la cita (usuario)
     */
    public function trabajador()
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }

    /**
     * Servicio reservado
     */
    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    /**
     * Cabina asignada (opcional)
     */
    public function cabina()
    {
        return $this->belongsTo(Cabina::class, 'cabina_id');
    }

    /* ==========================================================
       HELPERS
       ========================================================== */

    /**
     * Verifica si la cita está confirmada
     */
    public function estaConfirmada(): bool
    {
        return $this->estado === 'confirmada';
    }

    /**
     * Verifica si la cita está pendiente
     */
    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    /**
     * Etiqueta legible del estado (para mostrar en vistas)
     */
    public function etiquetaEstado(): string
    {
        return match ($this->estado) {
            'pendiente'   => 'Pendiente',
            'confirmada'  => 'Confirmada',
            'completada'  => 'Completada',
            'cancelada'   => 'Cancelada',
            default       => ucfirst($this->estado),
        };
    }
}