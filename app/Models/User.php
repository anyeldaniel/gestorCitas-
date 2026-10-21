<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Cita;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    /**
     * El nombre de la tabla asociada al modelo.
     * Esto soluciona el error de que Laravel buscaba la tabla 'users'.
     */
    protected $table = 'usuarios';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'nombre',
        'correo',
        'telefono',
        'contraseña',
        'rol',
        'foto', 
        'descripcion',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'contraseña',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'contraseña' => 'hashed',
        ];
    }

    /**
     * Obtener la contraseña para la autenticación.
     */
    public function getAuthPassword()
    {
        return $this->contraseña;
    }

    /**
     * Obtener el correo electrónico para el restablecimiento de contraseña.
     */
    public function getEmailForPasswordReset()
    {
        return $this->correo;
    }

    /**
     * Ruta de notificación por correo.
     */
    public function routeNotificationForMail($notification)
    {
        return $this->correo;
    }

    /**
     * Relación: citas donde este usuario es el trabajador asignado.
     */
    public function citasComoTrabajador()
    {
        return $this->hasMany(Cita::class, 'trabajador_id');
    }

    /**
     * Relación: servicios asignados a este especialista.
     * (Útil para la tabla intermedia que estamos creando)
     */
    public function serviciosAsignados()
    {
        return $this->belongsToMany(Servicio::class, 'servicio_usuario', 'usuario_id', 'servicio_id');
    }
}