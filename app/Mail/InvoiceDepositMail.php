<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail 11 — Facture acompte (restant du) */
class InvoiceDepositMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.factures_from', 'factures@marquage-textile.fr'),
            subject: 'Facture acompte — Commande ' . $this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice-deposit');
    }
}
