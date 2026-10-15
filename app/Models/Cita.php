<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'citas';

    protected $fillable = [
        'cliente_id',
        'trabajador_id',
        'servicio_id',
        'cabina_id',
        'monto_total',
        'fecha',
        'hora',
        'estado',
        'motivo_cancelacion',
        'cancelada_por',
        'cancelada_en',
    ];

    protected $casts = [
        'fecha'        => 'date',
        'monto_total'  => 'decimal:2',
        'cancelada_en' => 'datetime',
        'created_at'   => 'datetime',
        'updated_at'   => 'datetime',
    ];

    /* ==========================================================
       RELACIONES
       ========================================================== */

    public function cliente()
    {
        return $this->belongsTo(User::class, 'cliente_id');
    }

    public function trabajador()
    {
        return $this->belongsTo(User::class, 'trabajador_id');
    }

    public function servicio()
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function cabina()
    {
        return $this->belongsTo(Cabina::class, 'cabina_id');
    }

    public function canceladaPor()
    {
        return $this->belongsTo(User::class, 'cancelada_por');
    }

    /* ==========================================================
       HELPERS
       ========================================================== */

    public function estaCancelada(): bool
    {
        return $this->estado === 'cancelada';
    }

    public function estaConfirmada(): bool
    {
        return $this->estado === 'confirmada';
    }

    public function estaPendiente(): bool
    {
        return $this->estado === 'pendiente';
    }

    public function etiquetaEstado(): string
    {
        return match ($this->estado) {
            'pendiente'  => 'Pendiente',
            'confirmada' => 'Confirmada',
            'en curso'   => 'En curso',
            'completada' => 'Completada',
            'cancelada'  => 'Cancelada',
            default      => ucfirst($this->estado),
        };
    }

    public function claseEstado(): string
    {
        return 'estado-' . str_replace(' ', '-', $this->estado);
    }

    /**
     * Hora estimada de fin según duración del servicio.
     */
    public function horaFin(): ?string
    {
        if (!$this->servicio || !$this->servicio->duracion_minutos) {
            return null;
        }

        return \Carbon\Carbon::parse($this->hora)
            ->addMinutes((int) $this->servicio->duracion_minutos)
            ->format('H:i');
    }

    /**
     * Tiempo restante formateado (ej. "en 25 min" o "hace 10 min").
     * Se usa en las tarjetas de cita.
     */
    public function tiempoRelativo(): ?string
    {
        try {
            $inicio = \Carbon\Carbon::parse($this->fecha->toDateString() . ' ' . $this->hora);
            return $inicio->diffForHumans(now(), [
                'syntax'   => \Carbon\CarbonInterface::DIFF_RELATIVE_TO_NOW,
                'short'    => true,
                'parts'    => 1,
            ]);
        } catch (\Throwable $e) {
            return null;
        }
    }
}