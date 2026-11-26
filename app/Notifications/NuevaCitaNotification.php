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

    /**
     * Recibimos el objeto Cita para personalizar el mensaje.
     */
    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    /**
     * Solo enviamos por WebPush (nada de correos para evitar el error SMTP).
     */
    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    /**
     * Definimos el contenido de la notificación Push.
     */
    public function toWebPush($notifiable, $notification)
    {
        // Verificamos si quien recibe la notificación es el terapeuta
        // (Comparamos el ID del receptor con el ID del trabajador de la cita)
        $esTerapeuta = ($notifiable->id === $this->cita->trabajador_id);
        
        if ($esTerapeuta) {
            $titulo = '¡Nueva cita agendada!';
            $mensaje = 'Tienes una nueva cita para el ' . $this->cita->fecha . ' a las ' . $this->cita->hora;
        } else {
            $titulo = '¡Reserva exitosa!';
            $mensaje = 'Tu cita ha sido apartada. Por favor, completa el pago para confirmarla.';
        }

        return (new WebPushMessage)
            ->title($titulo)
            ->icon('/icons/icon-192x192.png')
            ->body($mensaje)
            ->action('Ver detalles', '/mis-reservas') // Ruta para el cliente
            ->data(['url' => $esTerapeuta ? '/agenda' : '/mis-reservas']); // Redirige a donde corresponda
    }
}