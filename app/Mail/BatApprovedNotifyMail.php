<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail 9 — Notification PAO: BAT valide par le client */
class BatApprovedNotifyMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[PAO] BAT valide par le client — Commande ' . $this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.bat-approved-notify');
    }
}
