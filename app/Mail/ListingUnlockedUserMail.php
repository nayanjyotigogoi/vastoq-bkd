<?php

namespace App\Mail;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingUnlockedUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user, public Listing $listing) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'You unlocked a property on Vastoq');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.listing-unlocked-user');
    }
}
