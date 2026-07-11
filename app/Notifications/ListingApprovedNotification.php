<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ListingApprovedNotification extends Notification
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
            'type'    => 'listing_approved',
            'title'   => 'Listing approved',
            'body'    => "Your listing \"{$this->listing->title}\" has been approved and is now live.",
            'url'     => "/rentals/{$this->listing->id}",
            'icon'    => 'check',
        ];
    }
}
