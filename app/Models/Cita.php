<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Cita extends Model
{
    use HasFactory;

    // Le dice a Laravel que use tu tabla de XAMPP
    protected $table = 'citas'; 

    protected $fillable = [
        'user_id',
        'servicio_id',
        'fecha',
        'hora',
        'estado',
    ];
}