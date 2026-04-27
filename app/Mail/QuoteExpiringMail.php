<?php

namespace App\Mail;

use App\Models\Quote;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail 4 — Relance expiration devis (J-7, J-2, J-1) */
class QuoteExpiringMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Quote $quote, public int $daysLeft) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.commandes_from', 'commandes@marquage-textile.fr'),
            subject: 'Votre devis ' . $this->quote->reference . ' expire dans ' . $this->daysLeft . ' jour' . ($this->daysLeft > 1 ? 's' : ''),
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-expiring');
    }
}
