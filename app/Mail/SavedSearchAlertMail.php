<?php

namespace App\Mail;

use App\Models\Listing;
use App\Models\SavedSearch;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SavedSearchAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public SavedSearch $savedSearch,
        public Listing $listing
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'New listing matches your search — ' . $this->savedSearch->name
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.saved-search-alert');
    }
}
