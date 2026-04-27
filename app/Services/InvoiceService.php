<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use horstoeko\zugferd\codelists\ZugferdCurrencyCodes;
use horstoeko\zugferd\codelists\ZugferdDutyTaxFeeCategories;
use horstoeko\zugferd\codelists\ZugferdInvoiceType;
use horstoeko\zugferd\ZugferdDocumentBuilder;
use horstoeko\zugferd\ZugferdDocumentPdfMerger;
use horstoeko\zugferd\ZugferdProfiles;
use Illuminate\Support\Facades\Storage;

class InvoiceService
{
    /**
     * Generate the invoice PDF with embedded Factur-X XML.
     */
    public function generate(Invoice $invoice): string
    {
        $invoice->load(['order.items.product', 'order.items.color', 'order.items.size', 'order.items.technique', 'order.user', 'order.quote']);

        $order = $invoice->order;

        // 1. Generate the visual PDF
        $pdf = Pdf::loadView('pdf.invoice', compact('invoice', 'order'))
            ->setPaper('a4')
            ->setOption('isPhpEnabled', true);

        $pdfContent = $pdf->output();

        // 2. Build Factur-X XML
        $xmlContent = $this->buildFacturXml($invoice, $order);

        // 3. Merge XML into PDF (Factur-X / ZUGFeRD)
        try {
            $merger = new ZugferdDocumentPdfMerger($xmlContent, $pdfContent);
            $merger->generateDocument();
            $pdfContent = $merger->downloadString();
        } catch (\Throwable $e) {
            // Si le merge echoue, on garde le PDF classique
            \Log::warning('Factur-X merge failed: ' . $e->getMessage());
        }

        // 4. Store
        $filename = 'invoices/' . $invoice->number . '.pdf';
        Storage::disk('local')->put($filename, $pdfContent);

        $invoice->update(['pdf_path' => $filename]);

        return $filename;
    }

    /**
     * Build the Factur-X XML string for an invoice.
     */
    private function buildFacturXml(Invoice $invoice, Order $order): string
    {
        $builder = ZugferdDocumentBuilder::createNew(ZugferdProfiles::PROFILE_EN16931);

        // Document info
        $builder->setDocumentInformation(
            $invoice->number,
            ZugferdInvoiceType::INVOICE,
            $invoice->issued_at,
            ZugferdCurrencyCodes::EURO,
        );

        // Seller
        $builder->setDocumentSeller('EIRL LEFEBVRE - LCS Marquage Textile');
        $builder->addDocumentSellerTaxRegistration('VA', 'FR54790260954');
        $builder->setDocumentSellerAddress('19 rue de la Resistance', '', '', '59155', 'Faches-Thumesnil', 'FR');
        $builder->setDocumentSellerContact('Service client', '', '0320400690', '', 'contact@marquage-textile.fr');

        // Buyer
        $buyerName = $order->user?->company ?? $order->user?->name ?? 'Client';
        $builder->setDocumentBuyer($buyerName);

        if ($order->customer_vat_number) {
            $builder->addDocumentBuyerTaxRegistration('VA', $order->customer_vat_number);
        }

        if ($invoice->billing_address) {
            $addr = $invoice->billing_address;
            $builder->setDocumentBuyerAddress(
                $addr['line1'] ?? '',
                $addr['line2'] ?? '',
                '',
                $addr['postal_code'] ?? '',
                $addr['city'] ?? '',
                $addr['country'] === 'France' ? 'FR' : ($addr['country'] ?? 'FR'),
            );
        }

        // Payment terms
        if ($invoice->payment_due_at) {
            $builder->addDocumentPaymentTerm('Paiement a reception', $invoice->payment_due_at);
        } else {
            $builder->addDocumentPaymentTerm('Paiement a reception');
        }

        // Order reference
        $builder->setDocumentBuyerOrderReferencedDocument($order->reference);

        // Line items
        $lineIndex = 0;
        foreach ($order->items as $item) {
            $lineIndex++;
            $productName = $item->product->name ?? 'Produit';
            $unitPrice = (float) $item->unit_price_ht + (float) $item->marking_price_ht;
            $lineTotal = (float) $item->line_total_ht;
            $quantity = (float) $item->quantity;

            $builder->addNewPosition((string) $lineIndex);
            $builder->setDocumentPositionProductDetails($productName, '', $item->product?->reference ?? '');
            $builder->setDocumentPositionNetPrice($unitPrice);
            $builder->setDocumentPositionQuantity($quantity, 'C62'); // C62 = unit

            // VAT per line
            if ($order->vat_exemption) {
                $builder->addDocumentPositionTax(ZugferdDutyTaxFeeCategories::ZERO_RATED_GOODS, 'VAT', 0);
            } else {
                $builder->addDocumentPositionTax(ZugferdDutyTaxFeeCategories::STANDARD_RATE, 'VAT', 20.0);
            }

            $builder->setDocumentPositionLineSummation($lineTotal);
        }

        // Shipping as charge if present
        if ((float) $order->shipping_ht > 0) {
            $builder->addDocumentAllowanceCharge(
                (float) $order->shipping_ht,
                true, // charge (not allowance)
                'SAA', // shipping and handling
                'Frais de livraison',
            );
        }

        // Tax summary
        if ($order->vat_exemption) {
            $builder->addDocumentTax(ZugferdDutyTaxFeeCategories::ZERO_RATED_GOODS, 'VAT', (float) $order->total_ht, 0, 0);
        } else {
            $builder->addDocumentTax(ZugferdDutyTaxFeeCategories::STANDARD_RATE, 'VAT', (float) $order->total_ht, (float) $order->total_tva, 20.0);
        }

        // Totals
        $builder->setDocumentSummation(
            (float) $order->total_ttc,  // grand total
            (float) $order->total_ttc,  // due payable
            (float) $order->subtotal_ht, // line total
            0,                           // charge total
            0,                           // allowance total
            (float) $order->total_ht,    // tax basis total
            (float) $order->total_tva,   // tax total
            0,                           // prepaid
            (float) $invoice->amount_remaining, // amount due
        );

        return $builder->getContent();
    }

    /**
     * Download the invoice PDF.
     */
    public function download(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        if (! $invoice->pdf_path || ! Storage::disk('local')->exists($invoice->pdf_path)) {
            $this->generate($invoice);
            $invoice->refresh();
        }

        return response()->download(
            Storage::disk('local')->path($invoice->pdf_path),
            'Facture-' . $invoice->number . '.pdf',
        );
    }

    /**
     * Stream the invoice PDF inline.
     */
    public function stream(Invoice $invoice): \Symfony\Component\HttpFoundation\Response
    {
        if (! $invoice->pdf_path || ! Storage::disk('local')->exists($invoice->pdf_path)) {
            $this->generate($invoice);
            $invoice->refresh();
        }

        return response()->file(
            Storage::disk('local')->path($invoice->pdf_path),
            ['Content-Type' => 'application/pdf'],
        );
    }
}
