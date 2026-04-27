<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail 7 — Paiement recu (virement valide) */
class PaymentReceivedMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.commandes_from', 'commandes@marquage-textile.fr'),
            subject: 'Paiement recu — Commande ' . $this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.payment-received');
    }
}
