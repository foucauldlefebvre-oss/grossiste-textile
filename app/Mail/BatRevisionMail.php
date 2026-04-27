<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail 6 — Nouveau BAT apres modification */
class BatRevisionMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.pao_from', 'pao@marquage-textile.fr'),
            subject: 'Nouveau BAT disponible — Commande ' . $this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.bat-revision');
    }
}
