<?php

namespace App\Mail;

use App\Models\Listing;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListingEnquiryMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Listing $listing,
        public string $tenantName,
        public string $tenantPhone,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'New enquiry on your property — Vastoq');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.listing-enquiry');
    }
}
