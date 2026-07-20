<?php

namespace App\Mail;

use App\Models\ContactReport;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ReportRefundedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public ContactReport $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your Report Has Been Approved — Points Refunded | Vastoq');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.report-refunded');
    }
}
