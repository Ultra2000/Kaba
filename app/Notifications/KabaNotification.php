<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class KabaNotification extends Notification
{
    use Queueable;

    /**
     * @param array{icon:string,color:string,message:string,url?:string,kind?:string} $payload
     */
    public function __construct(public array $payload)
    {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return $this->payload;
    }
}
