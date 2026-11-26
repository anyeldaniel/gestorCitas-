<?php

namespace App\Notifications;

use NotificationChannels\WebPush\WebPushMessage;

class CustomWebPushMessage extends WebPushMessage
{
    public function toArray()
    {
        // Envolvemos todo el payload dentro de "notification"
        return ['notification' => parent::toArray()];
    }
}