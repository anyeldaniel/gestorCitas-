<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notificacion extends Model
{
    protected $table = 'notificaciones';

    protected $fillable = ['user_id', 'tipo', 'titulo', 'mensaje', 'data', 'leida_at'];

    protected $casts = [
        'data'     => 'array',
        'leida_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeNoLeidas($q)
    {
        return $q->whereNull('leida_at');
    }

    public function marcarLeida(): void
    {
        if (is_null($this->leida_at)) {
            $this->update(['leida_at' => now()]);
        }
    }

    public function icono(): string
    {
        return match ($this->tipo) {
            'cita_nueva'      => 'calendar-plus',
            'cita_confirmada' => 'check-circle-2',
            'cita_cancelada'  => 'x-circle',
            'cita_en_curso'   => 'play-circle',
            'cita_completada' => 'check-check',
            default           => 'bell',
        };
    }

    public function color(): string
    {
        return match ($this->tipo) {
            'cita_cancelada'           => 'rojo',
            'cita_confirmada',
            'cita_completada'          => 'verde',
            'cita_en_curso'            => 'azul',
            'cita_nueva'               => 'violeta',
            default                    => 'gris',
        };
    }
}