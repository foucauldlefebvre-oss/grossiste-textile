<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteBatReadyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'BAT pret - Devis ' . $this->quote->reference,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.bat-ready',
        );
    }
}
