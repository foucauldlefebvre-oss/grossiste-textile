<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Quote;
use App\Services\InvoiceService;
use App\Services\OrderNotificationService;

class OrderService
{
    /**
     * Create an Order from an accepted/sent Quote.
     */
    public function createFromQuote(Quote $quote, ?float $shippingHt = 0): Order
    {
        $quote->load('items');

        if ($quote->items->isEmpty()) {
            throw new \InvalidArgumentException('Le devis est vide.');
        }

        $subtotalHt = (float) $quote->total_ht;
        $totalHt = $subtotalHt + $shippingHt;
        $totalTva = round($totalHt * QuoteService::TVA_RATE, 2);
        $totalTtc = round($totalHt + $totalTva, 2);

        // Detecter si le devis contient du marquage
        $hasMarking = $quote->items->contains(fn ($item) => (float) $item->marking_price_ht > 0);

        $order = Order::create([
            'reference' => $this->generateReference(),
            'user_id' => $quote->user_id,
            'quote_id' => $quote->id,
            'status' => 'pending',
            'payment_status' => 'pending',
            'has_marking' => $hasMarking,
            'bat_status' => $hasMarking ? 'pending' : 'none',
            'subtotal_ht' => $subtotalHt,
            'shipping_ht' => $shippingHt,
            'total_ht' => $totalHt,
            'total_tva' => $totalTva,
            'total_ttc' => $totalTtc,
        ]);

        // Copy quote items to order items
        foreach ($quote->items as $item) {
            $order->items()->create([
                'marking_group' => $item->marking_group ?? 0,
                'product_id' => $item->product_id,
                'product_color_id' => $item->product_color_id,
                'product_size_id' => $item->product_size_id,
                'marking_technique_id' => $item->marking_technique_id,
                'quantity' => $item->quantity,
                'unit_price_ht' => $item->unit_price_ht,
                'marking_price_ht' => $item->marking_price_ht,
                'line_total_ht' => $item->line_total_ht,
                'visual_file' => $item->visual_file,
                'marking_zone' => $item->marking_zone,
                'visual_colors' => $item->visual_colors,
            ]);
        }

        // Mark quote as accepted
        $quote->update([
            'status' => 'accepted',
            'accepted_at' => now(),
        ]);

        return $order;
    }

    /**
     * Mark order as paid and generate invoice.
     */
    public function markAsPaid(Order $order, ?string $stripePaymentIntentId = null, ?float $amountPaid = null): void
    {
        $paid = $amountPaid ?? (float) $order->total_ttc;
        $isFullPayment = $paid >= (float) $order->total_ttc;

        $order->update([
            'amount_paid' => (float) ($order->amount_paid ?? 0) + $paid,
            'payment_status' => $isFullPayment ? 'paid' : 'partial',
            'status' => 'confirmed',
            'stripe_payment_intent_id' => $stripePaymentIntentId ?? $order->stripe_payment_intent_id,
        ]);

        $order->updatePaymentStatus();

        // Generer la facture et le PDF
        $invoice = $this->generateInvoice($order->fresh());
        app(InvoiceService::class)->generate($invoice);

        if ($isFullPayment) {
            // Mail 2 : facture complete
            app(NotificationService::class)->sendInvoice($order->fresh(), $invoice->fresh());
        } else {
            // Mail 11 : facture acompte avec restant du
            app(NotificationService::class)->sendInvoiceDeposit($order->fresh(), $invoice->fresh());
            // Notifier le solde restant
            app(OrderNotificationService::class)->notifyBalanceDue($order);
        }

    }

    /**
     * Solder une commande : met a jour la facture en "soldee" et renvoie le PDF.
     */
    public function settleInvoice(Order $order): void
    {
        $order->update([
            'amount_paid' => (float) $order->total_ttc,
            'payment_status' => 'paid',
        ]);

        $invoice = $order->invoice;
        if (! $invoice) {
            return;
        }

        $invoice->update([
            'status' => 'settled',
            'amount_paid' => (float) $order->total_ttc,
            'amount_remaining' => 0,
        ]);

        // Regenerer le PDF avec le nouveau statut
        app(InvoiceService::class)->generate($invoice->fresh());

        // Mail 12 : facture soldee
        app(NotificationService::class)->sendInvoicePaid($order->fresh(), $invoice->fresh());
    }

    /**
     * Generer la facture manuellement (bouton "Facturer" back-office pour virements).
     */
    public function generateInvoiceManual(Order $order): Invoice
    {
        $invoice = $this->generateInvoice($order->fresh());
        app(InvoiceService::class)->generate($invoice);

        $order->fresh();
        $isDeposit = $invoice->status === 'deposit';

        if ($isDeposit) {
            app(NotificationService::class)->sendInvoiceDeposit($order->fresh(), $invoice->fresh());
        } else {
            app(NotificationService::class)->sendInvoice($order->fresh(), $invoice->fresh());
        }

        return $invoice;
    }

    /**
     * Generate an invoice for a paid order.
     */
    public function generateInvoice(Order $order): Invoice
    {
        $existing = $order->invoice;
        if ($existing) {
            return $existing;
        }

        $amountPaid = (float) ($order->amount_paid ?? 0);
        $totalTtc = (float) $order->total_ttc;
        $isDeposit = $order->payment_status === 'partial' || ($amountPaid > 0 && $amountPaid < $totalTtc);

        // Build billing address snapshot
        $billingAddr = $order->billingAddress;
        $billingSnapshot = $billingAddr ? [
            'line1' => $billingAddr->address_line_1,
            'line2' => $billingAddr->address_line_2,
            'postal_code' => $billingAddr->postal_code,
            'city' => $billingAddr->city,
            'country' => $billingAddr->country,
            'company' => $billingAddr->company,
        ] : null;

        return Invoice::create([
            'number' => $this->generateInvoiceNumber(),
            'order_id' => $order->id,
            'user_id' => $order->user_id,
            'type' => 'invoice',
            'status' => $isDeposit ? 'deposit' : 'paid',
            'payment_method' => $order->stripe_payment_intent_id ? 'cb' : 'wire_transfer',
            'total_ht' => $order->total_ht,
            'total_tva' => $order->total_tva,
            'total_ttc' => $totalTtc,
            'amount_paid' => $amountPaid,
            'amount_remaining' => max(0, $totalTtc - $amountPaid),
            'vat_number' => $order->customer_vat_number,
            'vat_exemption' => $order->vat_exemption,
            'customer_siret' => $order->customer_siret,
            'billing_address' => $billingSnapshot,
            'issued_at' => now(),
            'payment_due_at' => now()->addDays(30),
            'delivery_date' => $order->shipped_at?->toDateString() ?? $order->created_at->toDateString(),
        ]);
    }

    private function generateReference(): string
    {
        $prefix = 'CMD' . date('Ym');
        $last = Order::where('reference', 'like', $prefix . '%')
            ->orderByDesc('reference')
            ->value('reference');

        $number = $last
            ? ((int) substr($last, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'FA-' . date('Ym') . '-';
        $last = Invoice::where('number', 'like', $prefix . '%')
            ->orderByDesc('number')
            ->value('number');

        $number = $last
            ? ((int) substr($last, strlen($prefix))) + 1
            : 1;

        return $prefix . str_pad($number, 4, '0', STR_PAD_LEFT);
    }
}
