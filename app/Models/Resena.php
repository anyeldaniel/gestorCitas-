<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resena extends Model
{
    protected $table = 'resenas';

    protected $fillable = [
        'user_id',
        'terapeuta_id',
        'cita_id',
        'puntuacion',
        'comentario',
    ];

    /**
     * Cliente que escribió la reseña.
     */
    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Terapeuta que recibió la reseña.
     */
    public function terapeuta()
    {
        return $this->belongsTo(User::class, 'terapeuta_id');
    }

    /**
     * Cita asociada a la reseña (opcional).
     */
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}