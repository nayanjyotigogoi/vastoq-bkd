<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ListingUnlockedOwnerNotification extends Notification
{
    use Queueable;

    public function __construct(public Listing $listing) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'  => 'listing_unlocked',
            'title' => 'Someone viewed your contact',
            'body'  => "A tenant unlocked your contact details for \"{$this->listing->title}\".",
            'url'   => "/owner/dashboard",
            'icon'  => 'eye',
        ];
    }
}
