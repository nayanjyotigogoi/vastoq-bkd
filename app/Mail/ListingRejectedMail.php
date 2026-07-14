<?php

namespace App\Mail;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $owner, public Listing $listing, public string $reason) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Update on your Vastoq listing');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.listing-rejected');
    }
}
