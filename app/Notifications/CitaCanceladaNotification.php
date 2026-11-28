<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Models\Cita;

class CitaCanceladaNotification extends Notification
{
    use Queueable;

    protected $cita;

    public function __construct(Cita $cita)
    {
        $this->cita = $cita;
    }

    public function via($notifiable)
    {
        return [WebPushChannel::class];
    }

    public function toWebPush($notifiable, $notification)
    {
        $this->cita->loadMissing('pagos');
        $mensaje = 'Lamentamos informarte que tu cita del ' . $this->cita->fecha . ' a las ' . $this->cita->hora . ' ha sido cancelada.';

        if ($this->cita->motivo_cancelacion) {
            $mensaje .= ' Motivo: ' . $this->cita->motivo_cancelacion . '.';
        }

        $pagosConReembolso = $this->cita->pagos
            ->where('estado_pago', 'aprobado')
            ->whereIn('estado_reembolso', ['por_definir', 'pendiente']);
        $montoPendiente = $pagosConReembolso
            ->where('estado_reembolso', 'pendiente')
            ->sum('monto_reembolso');

        if ($montoPendiente > 0) {
            $mensaje .= ' Reembolso pendiente por $' . number_format((float) $montoPendiente, 2) . '.';
        }

        if ($pagosConReembolso->contains('estado_reembolso', 'por_definir')) {
            $mensaje .= ' Reembolso en revisión.';
        }

        return (new WebPushMessage)
            ->title('Cita Cancelada')
            ->icon('/icons/icon-192x192.png')
            ->body($mensaje)
            ->action('Ver mis reservas', '/mis-reservas')
            ->data(['url' => '/mis-reservas']);
    }
}