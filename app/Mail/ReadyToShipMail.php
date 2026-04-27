<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail 10 — Commande prete a expedier */
class ReadyToShipMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.commandes_from', 'commandes@marquage-textile.fr'),
            subject: 'Votre commande ' . $this->order->reference . ' est prete a etre expediee',
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.ready-to-ship');
    }
}
