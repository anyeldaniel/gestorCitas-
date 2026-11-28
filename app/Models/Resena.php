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

    // reseña que escribio el cliente
    public function cliente()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // reseña que recibio el terapeuta
    public function terapeuta()
    {
        return $this->belongsTo(User::class, 'terapeuta_id');
    }

    // ciota asociada a la reseña
    public function cita()
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }
}