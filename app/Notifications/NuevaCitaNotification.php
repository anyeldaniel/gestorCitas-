<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Models\Cita;

class NuevaCitaNotification extends Notification
{
    use Queueable;

    protected $cita;
    protected bool $esPagoTotal;

    // personalizar mensaje
    public function __construct(Cita $cita, bool $esPagoTotal = false)
    {
        $this->cita = $cita;
        $this->esPagoTotal = $esPagoTotal;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    // contenido de la notificacion
    public function toWebPush($notifiable, $notification)
    {
        // Verificamos si quien recibe la notificación es el terapeuta
        $esTerapeuta = ($notifiable->id === $this->cita->trabajador_id);
        
        if ($esTerapeuta) {
            $titulo = '¡Nueva cita agendada!';
            $mensaje = 'Tienes una nueva cita para el ' . $this->cita->fecha . ' a las ' . $this->cita->hora;
        } else {
            $titulo = '¡Reserva exitosa!';
            $mensaje = $this->esPagoTotal
                ? 'Tu cita ha sido apartada. Solo debes esperar la confirmación del equipo.'
                : 'Tu cita ha sido apartada. El reporte de tu abono está pendiente de confirmación.';
        }

        return (new WebPushMessage)
            ->title($titulo)
            ->icon('/icons/icon-192x192.png')
            ->body($mensaje)
            ->action('Ver detalles', '/mis-reservas') // Ruta para el cliente
            ->data(['url' => $esTerapeuta ? '/agenda' : '/mis-reservas']); // Redirige a donde corresponda
    }
}