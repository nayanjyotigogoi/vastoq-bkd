<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PaymentSuccessNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $description,
        public string $url = '/dashboard',
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'  => 'payment_success',
            'title' => 'Payment confirmed',
            'body'  => $this->description,
            'url'   => $this->url,
            'icon'  => 'rupee',
        ];
    }
}
