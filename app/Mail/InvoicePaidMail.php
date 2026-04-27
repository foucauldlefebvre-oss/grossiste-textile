<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/** Mail 12 — Facture soldee */
class InvoicePaidMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order, public Invoice $invoice) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: config('mail.factures_from', 'factures@marquage-textile.fr'),
            subject: 'Facture soldee — Commande ' . $this->order->reference,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.invoice-paid');
    }
}
