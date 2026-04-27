<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail 8 — Notification admin: nouvelle commande virement en attente */
class AdminNewOrderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.commandes_from', 'commandes@marquage-textile.fr'),
            subject: '[ADMIN] Nouvelle commande virement en attente — ' . $this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.admin-new-order');
    }
}
