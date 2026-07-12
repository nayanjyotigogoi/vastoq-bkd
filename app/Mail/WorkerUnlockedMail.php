<?php

namespace App\Mail;

use App\Models\Worker;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class WorkerUnlockedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $workerUser, public Worker $worker) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Someone viewed your contact details — Vastoq');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.worker-unlocked');
    }
}
