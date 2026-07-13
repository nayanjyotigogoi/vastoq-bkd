<?php

namespace App\Notifications;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class ListingEnquiryNotification extends Notification
{
    use Queueable;

    public function __construct(
        public Listing $listing,
        public string  $tenantName,
        public string  $tenantPhone,
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type'  => 'listing_enquiry',
            'title' => 'New enquiry on your listing',
            'body'  => "{$this->tenantName} ({$this->tenantPhone}) is interested in \"{$this->listing->title}\".",
            'url'   => '/owner/dashboard',
            'icon'  => 'phone',
        ];
    }
}
