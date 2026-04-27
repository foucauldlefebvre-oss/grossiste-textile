<?php

namespace App\Services;

use App\Mail\AdminNewOrderMail;
use App\Mail\BatApprovedNotifyMail;
use App\Mail\BatRevisionMail;
use App\Mail\InvoiceDepositMail;
use App\Mail\InvoicePaidMail;
use App\Mail\OrderConfirmationMail;
use App\Mail\OrderShippedMail;
use App\Mail\PaymentReceivedMail;
use App\Mail\QuoteBatReadyMail;
use App\Mail\QuoteExpiringMail;
use App\Mail\ReadyToShipMail;
use App\Mail\WelcomeMail;
use App\Mail\WireTransferPendingMail;
use App\Models\Invoice;
use App\Models\NotificationLog;
use App\Models\Order;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotificationService
{
    /** Mail 1 — Confirmation de commande (client) */
    public function sendOrderConfirmation(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) return;

        $this->sendMail(
            (new OrderConfirmationMail($order))->from(config('mail.commandes_from', 'commandes@marquage-textile.fr'), 'Marquage Textile'),
            $email, 'order_confirmation',
            'Confirmation de commande ' . $order->reference,
            $order->user_id,
        );
    }

    /** Mail 2 — Facture PDF (client) — called by OrderService */
    public function sendInvoice(Order $order, Invoice $invoice): void
    {
        $email = $order->user?->email;
        if (! $email) return;

        $mailable = (new InvoicePaidMail($order, $invoice))
            ->from(config('mail.factures_from', 'factures@marquage-textile.fr'), 'Marquage Textile');

        // Attach PDF if it exists
        if ($invoice->pdf_path && file_exists(storage_path('app/private/' . $invoice->pdf_path))) {
            $mailable->attach(storage_path('app/private/' . $invoice->pdf_path));
        }

        $this->sendMail($mailable, $email, 'invoice', 'Facture ' . $invoice->number, $order->user_id);
    }

    /** Mail 3 — En attente de virement (client) */
    public function sendWireTransferPending(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) return;

        $this->sendMail(
            new WireTransferPendingMail($order),
            $email, 'wire_transfer_pending',
            'En attente de virement — ' . $order->reference,
            $order->user_id,
        );
    }

    /** Mail 4 — Relance expiration devis (client) */
    public function sendQuoteExpiring(Quote $quote, int $daysLeft): void
    {
        $email = $quote->user?->email;
        if (! $email) return;

        $this->sendMail(
            (new QuoteExpiringMail($quote, $daysLeft))->from(config('mail.commandes_from', 'commandes@marquage-textile.fr'), 'Marquage Textile'),
            $email, 'quote_expiring_' . $daysLeft . 'd',
            'Devis ' . $quote->reference . ' expire dans ' . $daysLeft . 'j',
            $quote->user_id,
        );
    }

    /** Mail 5 — BAT pret (client) */
    public function sendBatReady(Quote $quote): void
    {
        $email = $quote->user?->email;
        if (! $email) return;

        $this->sendMail(
            (new QuoteBatReadyMail($quote))->from(config('mail.pao_from', 'pao@marquage-textile.fr'), 'Marquage Textile - PAO'),
            $email, 'bat_ready',
            'BAT pret — ' . $quote->reference,
            $quote->user_id,
        );
    }

    /** Mail 6 — Nouveau BAT apres modif (client) */
    public function sendBatRevision(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) return;

        $this->sendMail(
            (new BatRevisionMail($order))->from(config('mail.pao_from', 'pao@marquage-textile.fr'), 'Marquage Textile - PAO'),
            $email, 'bat_revision',
            'Nouveau BAT — ' . $order->reference,
            $order->user_id,
        );
    }

    /** Mail 7 — Paiement recu (client) */
    public function sendPaymentReceived(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) return;

        $this->sendMail(
            new PaymentReceivedMail($order),
            $email, 'payment_received',
            'Paiement recu — ' . $order->reference,
            $order->user_id,
        );
    }

    /** Mail 8 — Notification admin nouvelle commande (toutes) */
    public function notifyAdminNewOrder(Order $order): void
    {
        $recipients = [
            config('mail.commandes_from', 'commandes@marquage-textile.fr'),
            'textile.marquage@gmail.com',
        ];

        foreach (array_filter($recipients) as $email) {
            $this->sendMail(
                new AdminNewOrderMail($order),
                $email, 'admin_new_order',
                '[ADMIN] Commande virement — ' . $order->reference,
                null,
            );
        }
    }

    /** Mail 9 — BAT valide par client (a pao@) */
    public function notifyBatApproved(Order $order): void
    {
        $paoEmail = config('mail.pao_from', 'pao@marquage-textile.fr');

        $this->sendMail(
            new BatApprovedNotifyMail($order),
            $paoEmail, 'bat_approved_notify',
            '[PAO] BAT valide — ' . $order->reference,
            null,
        );
    }

    /** Mail 10 — Pret a expedier (client) */
    public function sendReadyToShip(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) return;

        $this->sendMail(
            new ReadyToShipMail($order),
            $email, 'ready_to_ship',
            'Commande prete — ' . $order->reference,
            $order->user_id,
        );
    }

    /** Mail 10bis — Commande expediee (client) */
    public function sendOrderShipped(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) return;

        $this->sendMail(
            (new OrderShippedMail($order))->from(config('mail.commandes_from', 'commandes@marquage-textile.fr'), 'Marquage Textile'),
            $email, 'order_shipped',
            'Commande expediee — ' . $order->reference,
            $order->user_id,
        );
    }

    /** Mail 11 — Facture acompte (client) */
    public function sendInvoiceDeposit(Order $order, Invoice $invoice): void
    {
        $email = $order->user?->email;
        if (! $email) return;

        $mailable = (new InvoiceDepositMail($order, $invoice))
            ->from(config('mail.factures_from', 'factures@marquage-textile.fr'), 'Marquage Textile');

        if ($invoice->pdf_path && file_exists(storage_path('app/private/' . $invoice->pdf_path))) {
            $mailable->attach(storage_path('app/private/' . $invoice->pdf_path));
        }

        $this->sendMail($mailable, $email, 'invoice_deposit', 'Facture acompte — ' . $order->reference, $order->user_id);
    }

    /** Mail 12 — Facture soldee (client) */
    public function sendInvoicePaid(Order $order, Invoice $invoice): void
    {
        $email = $order->user?->email;
        if (! $email) return;

        $mailable = (new InvoicePaidMail($order, $invoice))
            ->from(config('mail.factures_from', 'factures@marquage-textile.fr'), 'Marquage Textile');

        if ($invoice->pdf_path && file_exists(storage_path('app/private/' . $invoice->pdf_path))) {
            $mailable->attach(storage_path('app/private/' . $invoice->pdf_path));
        }

        $this->sendMail($mailable, $email, 'invoice_paid', 'Facture soldee — ' . $order->reference, $order->user_id);
    }

    /** Bienvenue */
    public function sendWelcome(User $user): void
    {
        $this->sendMail(
            new WelcomeMail($user),
            $user->email, 'welcome',
            'Bienvenue sur Marquage Textile',
            $user->id,
        );
    }

    // ─── Internal ────────────────────────────────────────────────

    private function sendMail($mailable, string $recipient, string $type, string $subject, ?int $userId): void
    {
        $log = NotificationLog::create([
            'user_id' => $userId,
            'type' => $type,
            'channel' => 'email',
            'recipient' => $recipient,
            'subject' => $subject,
            'status' => 'pending',
        ]);

        try {
            Mail::to($recipient)->send($mailable);
            $log->update(['status' => 'sent']);
        } catch (\Throwable $e) {
            Log::error('Notification failed: ' . $e->getMessage(), [
                'type' => $type,
                'recipient' => $recipient,
            ]);
            $log->update([
                'status' => 'failed',
                'error' => $e->getMessage(),
            ]);
        }
    }
}
