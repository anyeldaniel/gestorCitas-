<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pago extends Model
{
    use HasFactory;

    protected $table = 'pagos'; 

    protected $fillable = [
        'cita_id',
        'monto_anticipado',
        'metodo_pago',
        'banco_origen',
        'referencia',
        'telefono_pago',
        'comprobante_img',
        'estado_pago',
        'estado_reembolso',
        'monto_reembolso',
        'motivo_reembolso',
        'motivo_rechazo',
    ];

    protected $casts = [
        'monto_reembolso' => 'decimal:2',
    ];

    // un pago que pertenefce a una cita
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function metodoBancoLabel(): string
    {
        $metodo = match ($this->metodo_pago) {
            'c2p' => 'Pago móvil',
            default => ucfirst(str_replace('_', ' ', $this->metodo_pago)),
        };

        if ($this->metodo_pago !== 'c2p' || !$this->banco_origen) {
            return $metodo;
        }

        $bancos = [
            '0134' => 'Banesco',
            '0102' => 'Banco de Venezuela',
            '0105' => 'Banco Mercantil',
            '0108' => 'Banco Provincial',
            '0191' => 'Banco Nacional de Crédito (BNC)',
        ];

        return $metodo . ' / ' . ($bancos[$this->banco_origen] ?? $this->banco_origen);
    }
}