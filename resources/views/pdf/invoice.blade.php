<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Facture {{ $invoice->number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.4; }

        .page { padding: 30px 40px; }
        .header { display: table; width: 100%; margin-bottom: 25px; border-bottom: 3px solid #6b1d2a; padding-bottom: 15px; }
        .header-left { display: table-cell; vertical-align: middle; width: 50%; }
        .header-right { display: table-cell; vertical-align: middle; width: 50%; text-align: right; }
        .brand { font-size: 18px; font-weight: bold; color: #6b1d2a; }
        .brand-sub { font-size: 9px; color: #6b7280; margin-top: 2px; }
        .doc-title { font-size: 16px; font-weight: bold; color: #1f2937; }
        .doc-ref { font-size: 11px; color: #6b7280; margin-top: 3px; }

        .info-grid { display: table; width: 100%; margin-bottom: 20px; }
        .info-col { display: table-cell; width: 50%; vertical-align: top; }
        .info-box { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 4px; padding: 12px; margin-right: 10px; }
        .info-box:last-child { margin-right: 0; }
        .info-label { font-size: 8px; text-transform: uppercase; color: #9ca3af; letter-spacing: 0.5px; margin-bottom: 4px; }
        .info-value { font-size: 10px; color: #1f2937; }

        .section-title { font-size: 12px; font-weight: bold; color: #1f2937; margin: 20px 0 10px; padding-bottom: 5px; border-bottom: 1px solid #e5e7eb; }

        .status-banner { padding: 8px 15px; border-radius: 4px; margin-bottom: 15px; font-weight: bold; font-size: 11px; text-align: center; }
        .status-deposit { background: #dbeafe; border: 1px solid #93c5fd; color: #1e40af; }
        .status-settled { background: #ecfdf5; border: 1px solid #a7f3d0; color: #065f46; }

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items th { background: #6b1d2a; color: white; font-size: 8px; text-transform: uppercase; letter-spacing: 0.3px; padding: 8px 6px; text-align: left; }
        table.items td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; font-size: 9px; vertical-align: top; }
        table.items tr:nth-child(even) td { background: #f9fafb; }
        .text-right { text-align: right; }

        .totals { width: 300px; margin-left: auto; margin-top: 15px; }
        .totals table { width: 100%; border-collapse: collapse; }
        .totals td { padding: 6px 10px; font-size: 10px; }
        .totals .total-row td { border-top: 2px solid #6b1d2a; font-weight: bold; font-size: 13px; color: #6b1d2a; padding-top: 10px; }
        .totals .net-row td { border-top: 1px solid #e5e7eb; font-weight: bold; font-size: 12px; color: #1f2937; padding-top: 8px; }
        .totals .label { color: #6b7280; }

        .payment-info { background: #ecfdf5; border: 1px solid #a7f3d0; border-radius: 4px; padding: 12px; margin-top: 20px; }
        .payment-info .title { font-weight: bold; color: #065f46; font-size: 10px; margin-bottom: 4px; }
        .payment-info p { font-size: 9px; color: #047857; }

        .vat-notice { background: #fffbeb; border: 1px solid #fde68a; border-radius: 4px; padding: 10px; margin-top: 10px; font-size: 9px; color: #92400e; }

        .legal { margin-top: 25px; padding-top: 10px; border-top: 1px solid #e5e7eb; font-size: 8px; color: #9ca3af; line-height: 1.5; }

        .footer { position: fixed; bottom: 20px; left: 40px; right: 40px; text-align: center; font-size: 8px; color: #9ca3af; border-top: 1px solid #e5e7eb; padding-top: 10px; }
    </style>
</head>
<body>
    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div class="header-left">
                <div class="brand">LCS Marquage Textile</div>
                <div class="brand-sub">Personnalisation textile professionnelle</div>
            </div>
            <div class="header-right">
                <div class="doc-title">FACTURE</div>
                <div class="doc-ref">N&deg; {{ $invoice->number }}</div>
                <div class="doc-ref">Date d'emission : {{ $invoice->issued_at->format('d/m/Y') }}</div>
                @if($invoice->payment_method === 'wire_transfer')
                    <div class="doc-ref">Date d'echeance : {{ $invoice->payment_due_at?->format('d/m/Y') ?? 'A reception' }}</div>
                @endif
            </div>
        </div>

        {{-- Status banner --}}
        @if($invoice->status === 'deposit')
            <div class="status-banner status-deposit">FACTURE D'ACOMPTE (50%)</div>
        @elseif($invoice->status === 'settled')
            <div class="status-banner status-settled">FACTURE SOLDEE</div>
        @endif

        {{-- Info grid --}}
        <div class="info-grid">
            <div class="info-col">
                <div class="info-box">
                    <div class="info-label">Emetteur</div>
                    <div class="info-value">
                        <strong>EIRL LEFEBVRE</strong><br>
                        LCS Marquage Textile<br>
                        19 rue de la Resistance<br>
                        59155 Faches-Thumesnil, France<br>
                        Tel : 03 20 40 06 90<br>
                        contact@marquage-textile.fr<br>
                        SIRET : 794 260 954 00033<br>
                        TVA intra : FR54 790260954
                    </div>
                </div>
            </div>
            <div class="info-col">
                <div class="info-box">
                    <div class="info-label">Client</div>
                    <div class="info-value">
                        @if($order->user)
                            <strong>{{ $order->user->name }}</strong><br>
                            @if($order->user->company)
                                {{ $order->user->company }}<br>
                            @endif
                            {{ $order->user->email }}<br>
                        @endif
                        @if($invoice->billing_address)
                            {{ $invoice->billing_address['line1'] ?? '' }}<br>
                            @if(!empty($invoice->billing_address['line2']))
                                {{ $invoice->billing_address['line2'] }}<br>
                            @endif
                            {{ $invoice->billing_address['postal_code'] ?? '' }} {{ $invoice->billing_address['city'] ?? '' }}<br>
                            {{ $invoice->billing_address['country'] ?? 'France' }}
                        @endif
                        @if($order->customer_siret)
                            <br>SIRET : {{ $order->customer_siret }}
                        @endif
                        @if($order->customer_vat_number)
                            <br>TVA intra : {{ $order->customer_vat_number }}
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="doc-ref" style="margin-bottom: 15px;">
            Commande : {{ $order->reference }}
            @if($order->quote)
                &mdash; Devis : {{ $order->quote->reference }}
            @endif
            @if($invoice->payment_method)
                &mdash; Mode de paiement : {{ $invoice->payment_method === 'cb' ? 'Carte bancaire' : 'Virement bancaire' }}
            @endif
        </div>

        {{-- Items table --}}
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 35%;">Designation</th>
                    <th>Details</th>
                    <th class="text-right">P.U. HT</th>
                    <th class="text-right">Marquage HT</th>
                    <th class="text-right">Qte</th>
                    <th class="text-right">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            {{ $item->product->name ?? 'Produit' }}
                            @if($item->product?->reference)
                                <br><span style="color: #9ca3af; font-size: 8px;">Ref: {{ $item->product->reference }}</span>
                            @endif
                        </td>
                        <td>
                            @if($item->color) {{ $item->color->name }} @endif
                            @if($item->size) - {{ $item->size->size }} @endif
                            @if($item->technique) <br>{{ $item->technique->name }} @endif
                        </td>
                        <td class="text-right">{{ number_format($item->unit_price_ht, 2, ',', ' ') }} &euro;</td>
                        <td class="text-right">{{ number_format($item->marking_price_ht, 2, ',', ' ') }} &euro;</td>
                        <td class="text-right">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->line_total_ht, 2, ',', ' ') }} &euro;</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Totals --}}
        <div class="totals">
            <table>
                <tr>
                    <td class="label">Sous-total HT</td>
                    <td class="text-right">{{ number_format($order->subtotal_ht, 2, ',', ' ') }} &euro;</td>
                </tr>
                @if($order->shipping_ht > 0)
                    <tr>
                        <td class="label">
                            Livraison HT
                            @if($order->shipping_zone && $order->shipping_zone !== 'france')
                                ({{ \App\Services\QuoteService::SHIPPING_ZONES[$order->shipping_zone]['label'] ?? '' }})
                            @endif
                        </td>
                        <td class="text-right">{{ number_format($order->shipping_ht, 2, ',', ' ') }} &euro;</td>
                    </tr>
                @endif
                <tr>
                    <td class="label">Total HT</td>
                    <td class="text-right">{{ number_format($order->total_ht, 2, ',', ' ') }} &euro;</td>
                </tr>
                @if($order->vat_exemption)
                    <tr>
                        <td class="label">TVA</td>
                        <td class="text-right">0,00 &euro;</td>
                    </tr>
                @else
                    <tr>
                        <td class="label">TVA (20%)</td>
                        <td class="text-right">{{ number_format($order->total_tva, 2, ',', ' ') }} &euro;</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>Total TTC</td>
                    <td class="text-right">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</td>
                </tr>
                @if($invoice->status === 'deposit' || $invoice->status === 'settled')
                    @if((float) $invoice->amount_paid > 0)
                        <tr>
                            <td class="label">Acompte recu</td>
                            <td class="text-right">- {{ number_format($invoice->amount_paid, 2, ',', ' ') }} &euro;</td>
                        </tr>
                    @endif
                    <tr class="net-row">
                        <td>Montant net a payer</td>
                        <td class="text-right">{{ number_format($invoice->amount_remaining, 2, ',', ' ') }} &euro;</td>
                    </tr>
                @endif
            </table>
        </div>

        {{-- VAT exemption notice --}}
        @if($order->vat_exemption === 'intra_eu')
            <div class="vat-notice">
                <strong>Exoneration de TVA, article 262 ter I du CGI</strong> — Autoliquidation de la TVA. La TVA est due par le preneur (client).
            </div>
        @elseif($order->vat_exemption === 'export')
            <div class="vat-notice">
                <strong>Exoneration de TVA, article 262 I du CGI</strong> — Exportation hors Union europeenne. Livraison en conditions DAP.
            </div>
        @elseif($order->vat_exemption === 'dom_tom')
            <div class="vat-notice">
                <strong>Exoneration de TVA, article 294 du CGI</strong> — Livraison dans les departements et territoires d'outre-mer.
            </div>
        @endif

        {{-- Payment info --}}
        @if($order->payment_status === 'paid' || $invoice->status === 'settled')
            <div class="payment-info">
                <div class="title">Paiement recu</div>
                <p>
                    @if($invoice->payment_method === 'cb')
                        Reglement par carte bancaire.
                        @if($order->stripe_payment_intent_id)
                            Ref. paiement : {{ $order->stripe_payment_intent_id }}
                        @endif
                    @else
                        Reglement recu par virement bancaire.
                    @endif
                </p>
            </div>
        @elseif($invoice->status === 'deposit')
            <div class="payment-info" style="background: #dbeafe; border-color: #93c5fd;">
                <div class="title" style="color: #1e40af;">Acompte recu</div>
                <p style="color: #1e3a8a;">
                    Acompte de {{ number_format($invoice->amount_paid, 2, ',', ' ') }} &euro; recu.
                    Solde de {{ number_format($invoice->amount_remaining, 2, ',', ' ') }} &euro; a regler avant expedition.
                </p>
            </div>
        @endif

        {{-- Legal mentions --}}
        <div class="legal">
            <strong>Conditions de reglement :</strong> Paiement a reception de facture. Reglement par virement bancaire ou carte bancaire.<br>
            <strong>Penalites de retard :</strong> En cas de retard de paiement, une penalite egale a 3 fois le taux d'interet legal sera appliquee,
            ainsi qu'une indemnite forfaitaire de recouvrement de 40 &euro; (art. L441-10 et D441-5 du Code de commerce).<br>
            <strong>Escompte :</strong> Aucun escompte n'est accorde en cas de paiement anticipe.<br>
            <strong>Coordonnees bancaires :</strong> IBAN FR76 3000 4008 2600 0105 4843 748 — BIC BNPAFRPP
        </div>
    </div>

    <div class="footer">
        EIRL LEFEBVRE — LCS Marquage Textile — SIRET 794 260 954 00033 — TVA FR54 790260954 — RCS Lille Metropole<br>
        19 rue de la Resistance, 59155 Faches-Thumesnil — Tel : 03 20 40 06 90 — contact@marquage-textile.fr
    </div>
</body>
</html>
