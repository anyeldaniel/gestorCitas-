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
        return (new WebPushMessage)
            ->title('❌ Cita Cancelada')
            ->icon('/icons/icon-192x192.png')
            ->body('Lamentamos informarte que tu cita del ' . $this->cita->fecha . ' a las ' . $this->cita->hora . ' ha sido cancelada.')
            ->action('Ver mis reservas', '/mis-reservas')
            ->data(['url' => '/mis-reservas']);
    }
}