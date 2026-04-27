<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>BAT - {{ $quote->reference }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #1a1a1a; line-height: 1.4; }

        .page { padding: 30px 40px; }
        .header { display: table; width: 100%; margin-bottom: 25px; border-bottom: 3px solid #2563eb; padding-bottom: 15px; }
        .header-left { display: table-cell; vertical-align: middle; width: 50%; }
        .header-right { display: table-cell; vertical-align: middle; width: 50%; text-align: right; }
        .brand { font-size: 18px; font-weight: bold; color: #2563eb; }
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

        table.items { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
        table.items th { background: #2563eb; color: white; font-size: 8px; text-transform: uppercase; letter-spacing: 0.3px; padding: 8px 6px; text-align: left; }
        table.items td { padding: 8px 6px; border-bottom: 1px solid #e5e7eb; font-size: 9px; vertical-align: top; }
        table.items tr:nth-child(even) td { background: #f9fafb; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }

        .color-dot { display: inline-block; width: 10px; height: 10px; border-radius: 50%; border: 1px solid #d1d5db; vertical-align: middle; margin-right: 4px; }

        .totals { width: 250px; margin-left: auto; margin-bottom: 20px; }
        .totals-row { display: table; width: 100%; padding: 4px 0; }
        .totals-label { display: table-cell; color: #6b7280; font-size: 10px; }
        .totals-value { display: table-cell; text-align: right; font-size: 10px; }
        .totals-total { border-top: 2px solid #2563eb; padding-top: 6px; margin-top: 4px; }
        .totals-total .totals-label, .totals-total .totals-value { font-size: 13px; font-weight: bold; color: #2563eb; }

        .zones-grid { display: table; width: 100%; margin-bottom: 15px; }
        .zone-item { display: table-cell; text-align: center; padding: 8px; background: #f0f9ff; border: 1px solid #bfdbfe; border-radius: 4px; margin: 0 4px; }
        .zone-label { font-size: 8px; color: #3b82f6; text-transform: uppercase; }
        .zone-value { font-size: 10px; font-weight: bold; color: #1e40af; margin-top: 2px; }

        .approval { margin-top: 30px; border: 2px solid #2563eb; border-radius: 6px; padding: 20px; }
        .approval-title { font-size: 12px; font-weight: bold; color: #2563eb; margin-bottom: 10px; }
        .approval-grid { display: table; width: 100%; }
        .approval-col { display: table-cell; width: 50%; vertical-align: top; padding: 10px; }
        .sig-line { border-bottom: 1px solid #9ca3af; margin-top: 40px; margin-bottom: 4px; }
        .sig-label { font-size: 8px; color: #9ca3af; }

        .footer { margin-top: 25px; padding-top: 10px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 8px; color: #9ca3af; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 8px; font-weight: bold; }
        .badge-technique { background: #dbeafe; color: #1d4ed8; }
        .badge-zone { background: #fef3c7; color: #92400e; }

        .note-box { background: #fffbeb; border: 1px solid #fcd34d; border-radius: 4px; padding: 10px; margin-bottom: 15px; font-size: 9px; color: #92400e; }
    </style>
</head>
<body>
    <div class="page">
        {{-- Header --}}
        <div class="header">
            <div class="header-left">
                <div class="brand">Marquage Textile</div>
                <div class="brand-sub">Personnalisation textile professionnelle</div>
            </div>
            <div class="header-right">
                <div class="doc-title">BON A TIRER</div>
                <div class="doc-ref">{{ $quote->reference }} &mdash; {{ now()->format('d/m/Y') }}</div>
            </div>
        </div>

        {{-- Client + Quote info --}}
        <div class="info-grid">
            <div class="info-col">
                <div class="info-box">
                    <div class="info-label">Client</div>
                    @if($quote->user)
                        <div class="info-value font-bold">{{ $quote->user->name }}</div>
                        <div class="info-value">{{ $quote->user->email }}</div>
                    @else
                        <div class="info-value">Visiteur (session)</div>
                    @endif
                </div>
            </div>
            <div class="info-col">
                <div class="info-box">
                    <div class="info-label">Devis</div>
                    <div class="info-value font-bold">{{ $quote->reference }}</div>
                    <div class="info-value">
                        Statut : {{ match($quote->status) { 'draft' => 'Brouillon', 'sent' => 'Envoye', 'accepted' => 'Accepte', 'refused' => 'Refuse', 'expired' => 'Expire', default => $quote->status } }}
                    </div>
                    @if($quote->expires_at)
                        <div class="info-value">Expire le {{ $quote->expires_at->format('d/m/Y') }}</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Notes --}}
        @if($quote->notes)
            <div class="note-box">
                <strong>Notes :</strong> {{ $quote->notes }}
            </div>
        @endif

        {{-- Items table --}}
        <div class="section-title">Detail des articles</div>
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 25%;">Produit</th>
                    <th>Coloris</th>
                    <th>Taille</th>
                    <th>Technique</th>
                    <th>Zone</th>
                    <th class="text-center">Qte</th>
                    <th class="text-right">P.U. HT</th>
                    <th class="text-right">Marquage HT</th>
                    <th class="text-right">Total HT</th>
                </tr>
            </thead>
            <tbody>
                @foreach($quote->items as $item)
                    @php
                        $zoneLabels = [
                            'poitrine_gauche' => 'Poitrine G',
                            'poitrine_droite' => 'Poitrine D',
                            'dos' => 'Dos',
                            'dos_haut' => 'Dos (haut)',
                            'manche_gauche' => 'Manche G',
                            'manche_droite' => 'Manche D',
                            'col' => 'Col',
                            'face' => 'Face',
                            'cote' => 'Cote',
                        ];
                    @endphp
                    <tr>
                        <td>
                            <span class="font-bold">{{ $item->product->name }}</span><br>
                            <span style="color: #6b7280;">Ref. {{ $item->product->reference }}</span>
                        </td>
                        <td>
                            @if($item->color)
                                <span class="color-dot" style="background-color: {{ $item->color->hex_code }};"></span>
                                {{ $item->color->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $item->size?->size ?? '-' }}</td>
                        <td>
                            @if($item->technique)
                                <span class="badge badge-technique">{{ $item->technique->name }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @if($item->marking_zone)
                                <span class="badge badge-zone">{{ $zoneLabels[$item->marking_zone] ?? $item->marking_zone }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="text-center">{{ $item->quantity }}</td>
                        <td class="text-right">{{ number_format($item->unit_price_ht, 2, ',', ' ') }} &euro;</td>
                        <td class="text-right">{{ number_format($item->marking_price_ht, 2, ',', ' ') }} &euro;</td>
                        <td class="text-right font-bold">{{ number_format($item->line_total_ht, 2, ',', ' ') }} &euro;</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Marking zones summary --}}
        @php
            $techniques = $quote->items->filter(fn($i) => $i->technique)->groupBy(fn($i) => $i->technique->name);
            $zones = $quote->items->filter(fn($i) => $i->marking_zone)->pluck('marking_zone')->unique();
        @endphp

        @if($techniques->isNotEmpty())
            <div class="section-title">Techniques de marquage</div>
            <div style="margin-bottom: 15px;">
                @foreach($techniques as $techName => $techItems)
                    <div style="display: inline-block; margin-right: 15px; padding: 8px 12px; background: #f0f9ff; border: 1px solid #bfdbfe; border-radius: 4px;">
                        <span class="font-bold" style="color: #1e40af;">{{ $techName }}</span>
                        <span style="color: #6b7280; margin-left: 5px;">{{ $techItems->sum('quantity') }} pieces</span>
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Totals --}}
        <div class="totals">
            <div class="totals-row">
                <span class="totals-label">Sous-total HT</span>
                <span class="totals-value">{{ number_format($quote->total_ht, 2, ',', ' ') }} &euro;</span>
            </div>
            <div class="totals-row">
                <span class="totals-label">TVA (20%)</span>
                <span class="totals-value">{{ number_format($quote->total_tva, 2, ',', ' ') }} &euro;</span>
            </div>
            <div class="totals-row totals-total">
                <span class="totals-label">Total TTC</span>
                <span class="totals-value">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
            </div>
        </div>

        {{-- Approval section --}}
        <div class="approval">
            <div class="approval-title">Bon pour accord</div>
            <p style="font-size: 9px; color: #6b7280; margin-bottom: 10px;">
                En signant ce document, vous acceptez le bon a tirer tel que presente ci-dessus.
                Toute modification apres approbation pourra entrainer un delai et des frais supplementaires.
            </p>
            <div class="approval-grid">
                <div class="approval-col">
                    <div class="info-label">Date</div>
                    <div class="sig-line"></div>
                    <div class="sig-label">JJ / MM / AAAA</div>
                </div>
                <div class="approval-col">
                    <div class="info-label">Signature et cachet</div>
                    <div class="sig-line"></div>
                    <div class="sig-label">Mention "Bon pour accord"</div>
                </div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="footer">
            Marquage Textile &mdash; contact@marquage-textile.fr &mdash; Document genere le {{ now()->format('d/m/Y a H:i') }}
            <br>Ce document est un bon a tirer. Il ne constitue pas une facture.
        </div>
    </div>
</body>
</html>
