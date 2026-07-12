<?php

namespace App\Mail;

use App\Models\Listing;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingUnlockedOwnerMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $owner, public Listing $listing, public User $tenant) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Someone contacted you about your property — Vastoq');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.listing-unlocked-owner');
    }
}
