<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Mail;

class OrderNotificationService
{
    /**
     * BAT uploade par PAO → notifier le client.
     */
    public function notifyClientBatReady(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) {
            return;
        }

        $url = route('account.orders.show', $order->reference);

        Mail::raw(
            "Bonjour,\n\n"
            . "Le Bon A Tirer de votre commande {$order->reference} est disponible dans votre espace client.\n\n"
            . "Consultez-le et validez-le ici : {$url}\n\n"
            . "Cordialement,\nMarquage Textile",
            fn ($m) => $m
                ->from(config('mail.pao_from'), 'Marquage Textile - PAO')
                ->to($email)
                ->subject("BAT disponible — Commande {$order->reference}")
        );
    }

    /**
     * Client valide le BAT → notifier contact + pao.
     */
    public function notifyBatApproved(Order $order): void
    {
        $body = "Le BAT de la commande {$order->reference} a ete APPROUVE par le client ({$order->user?->name}).";

        foreach ([config('mail.devis_to'), config('mail.pao_from')] as $to) {
            Mail::raw($body, fn ($m) => $m->to($to)->subject("BAT APPROUVE — {$order->reference}"));
        }
    }

    /**
     * Client demande modification BAT → notifier pao uniquement.
     */
    public function notifyBatRevisionRequested(Order $order): void
    {
        $comment = $order->bat_client_comment ?? '(aucun commentaire)';

        Mail::raw(
            "Le client ({$order->user?->name}) demande une modification du BAT pour la commande {$order->reference}.\n\n"
            . "Commentaire :\n{$comment}",
            fn ($m) => $m
                ->to(config('mail.pao_from'))
                ->subject("BAT A MODIFIER — {$order->reference}")
        );
    }

    /**
     * Facture payee → envoyer au client depuis factures@.
     */
    public function sendInvoice(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) {
            return;
        }

        $invoice = $order->invoice;
        if (! $invoice || ! $invoice->pdf_path) {
            return;
        }

        $pdfPath = storage_path('app/private/' . $invoice->pdf_path);

        Mail::raw(
            "Bonjour,\n\n"
            . "Veuillez trouver ci-joint la facture {$invoice->number} pour votre commande {$order->reference}.\n\n"
            . "Montant : " . number_format($order->total_ttc, 2, ',', ' ') . " EUR TTC\n\n"
            . "Cordialement,\nMarquage Textile",
            function ($m) use ($email, $order, $invoice, $pdfPath) {
                $m->from(config('mail.factures_from'), 'Marquage Textile - Facturation')
                    ->to($email)
                    ->subject("Facture {$invoice->number} — Commande {$order->reference}");

                if (file_exists($pdfPath)) {
                    $m->attach($pdfPath, ['as' => "facture-{$invoice->number}.pdf", 'mime' => 'application/pdf']);
                }
            }
        );
    }

    /**
     * Acompte 50% recu → notifier le solde restant.
     */
    public function notifyBalanceDue(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) {
            return;
        }

        $remaining = (float) $order->total_ttc - (float) $order->amount_paid;
        if ($remaining <= 0) {
            return;
        }

        Mail::raw(
            "Bonjour,\n\n"
            . "Nous avons bien recu votre acompte de " . number_format($order->amount_paid, 2, ',', ' ') . " EUR pour la commande {$order->reference}.\n\n"
            . "Le solde restant de " . number_format($remaining, 2, ',', ' ') . " EUR sera a regler avant le depart de l'atelier.\n\n"
            . "Cordialement,\nMarquage Textile",
            fn ($m) => $m
                ->from(config('mail.factures_from'), 'Marquage Textile - Facturation')
                ->to($email)
                ->subject("Solde restant — Commande {$order->reference}")
        );
    }

    /**
     * Commande prete a expedier → notifier le client.
     */
    public function notifyReadyToShip(Order $order): void
    {
        $email = $order->user?->email;
        if (! $email) {
            return;
        }

        Mail::raw(
            "Bonjour,\n\n"
            . "Votre commande {$order->reference} est prete a etre expediee.\n\n"
            . "Vous recevrez un email avec le numero de suivi des l'expedition.\n\n"
            . "Cordialement,\nMarquage Textile",
            fn ($m) => $m
                ->from(config('mail.commandes_from'), 'Marquage Textile - Commandes')
                ->to($email)
                ->subject("Commande prete — {$order->reference}")
        );
    }
}
