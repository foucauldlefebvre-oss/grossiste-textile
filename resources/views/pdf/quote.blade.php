<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #2D2D2D; line-height: 1.5; }

        .header { background: #8B1A1A; color: white; padding: 24px 32px; }
        .header h1 { font-size: 20px; font-weight: bold; margin-bottom: 4px; }
        .header p { font-size: 10px; opacity: 0.8; }

        .meta-row { display: table; width: 100%; padding: 16px 32px; border-bottom: 1px solid #eee; }
        .meta-col { display: table-cell; width: 50%; vertical-align: top; }
        .meta-col h3 { font-size: 10px; text-transform: uppercase; color: #8B1A1A; letter-spacing: 1px; margin-bottom: 6px; }
        .meta-col p { font-size: 10px; color: #555; margin-bottom: 2px; }

        .validity { background: #FDF2F2; border: 1px solid #F9CDCD; padding: 10px 32px; font-size: 10px; color: #8B1A1A; }

        .section-title { font-size: 9px; text-transform: uppercase; letter-spacing: 1.5px; color: #999; padding: 12px 32px 4px; font-weight: bold; }

        .product-header { padding: 8px 32px; display: table; width: 100%; }
        .product-header .name { font-weight: bold; font-size: 12px; }
        .product-header .color { font-size: 10px; color: #888; }

        table.lines { width: 100%; border-collapse: collapse; margin: 0; }
        table.lines th { font-size: 9px; text-transform: uppercase; color: #999; letter-spacing: 0.5px; border-bottom: 1px solid #ddd; padding: 6px 8px; text-align: left; }
        table.lines th.r { text-align: right; }
        table.lines th.c { text-align: center; }
        table.lines td { padding: 5px 8px; font-size: 10px; border-bottom: 1px solid #f0f0f0; }
        table.lines td.r { text-align: right; }
        table.lines td.c { text-align: center; }
        table.lines tr.marking td { background: #FFFBEB; color: #92400E; font-size: 9px; }
        table.lines tr.marking td.label { padding-left: 20px; }

        .bloc-subtotal { padding: 6px 32px; background: #f9f9f9; border-top: 1px solid #eee; font-size: 10px; display: table; width: 100%; }
        .bloc-subtotal .left { display: table-cell; color: #888; }
        .bloc-subtotal .right { display: table-cell; text-align: right; font-weight: bold; }

        .logos-section { padding: 6px 32px 12px; font-size: 9px; color: #888; }
        .logos-section .logo-line { margin-bottom: 2px; }
        .logos-section .tech { color: #92400E; font-weight: bold; }

        .totals { padding: 16px 32px; border-top: 2px solid #8B1A1A; }
        .totals table { width: 240px; margin-left: auto; }
        .totals td { padding: 3px 0; font-size: 11px; }
        .totals td.r { text-align: right; }
        .totals tr.total td { font-size: 14px; font-weight: bold; border-top: 1px solid #ddd; padding-top: 8px; }
        .totals tr.total td.r { color: #8B1A1A; }

        .footer { position: fixed; bottom: 0; left: 0; right: 0; padding: 12px 32px; background: #f5f5f5; border-top: 1px solid #ddd; font-size: 8px; color: #999; text-align: center; }

        .page-break { page-break-before: always; }
    </style>
</head>
<body>
    {{-- Header --}}
    <div class="header">
        <h1>DEVIS {{ $quote->reference }}</h1>
        <p>marquage-textile.fr &mdash; Personnalisation textile professionnelle</p>
    </div>

    {{-- Meta --}}
    <div class="meta-row">
        <div class="meta-col">
            <h3>Emetteur</h3>
            <p><strong>LCS Marquage Textile</strong></p>
            <p>EIRL LEFEBVRE</p>
            <p>19 rue de la Resistance</p>
            <p>59155 Faches-Thumesnil</p>
            <p>Tel : 03 20 40 06 90</p>
            <p>TVA : FR54 790260954</p>
        </div>
        <div class="meta-col">
            <h3>Client</h3>
            <p><strong>{{ $quote->user?->name ?? 'Client non connecte' }}</strong></p>
            @if($quote->user?->company)<p>{{ $quote->user->company }}</p>@endif
            @if($quote->user?->email)<p>{{ $quote->user->email }}</p>@endif
            @if($quote->user?->phone)<p>{{ $quote->user->phone }}</p>@endif
            @if($quote->user?->siret)<p>SIRET : {{ $quote->user->siret }}</p>@endif
            @if($quote->user?->vat_number)<p>TVA intra : {{ $quote->user->vat_number }}</p>@endif
            @php
                $defaultAddress = $quote->user?->addresses()->where('is_default', true)->first()
                    ?? $quote->user?->addresses()->first();
            @endphp
            @if($defaultAddress)
                <p>{{ $defaultAddress->address_line_1 }}</p>
                <p>{{ $defaultAddress->postal_code }} {{ $defaultAddress->city }}</p>
            @endif
        </div>
    </div>

    <div class="meta-row" style="border-bottom: none; padding-bottom: 0;">
        <div class="meta-col">
            <h3>Devis</h3>
            <p>Reference : <strong>{{ $quote->reference }}</strong></p>
            <p>Date : {{ $quote->created_at->format('d/m/Y') }}</p>
            @if($quote->expires_at)<p>Valable jusqu'au : <strong>{{ $quote->expires_at->format('d/m/Y') }}</strong></p>@endif
        </div>
    </div>

    {{-- Validity banner --}}
    @if($quote->expires_at)
        <div class="validity">
            Ce devis est valable jusqu'au <strong>{{ $quote->expires_at->format('d/m/Y') }}</strong> ({{ $quote->daysRemaining() }} jours restants).
            Passe ce delai, les tarifs devront etre reconfirmes.
        </div>
    @endif

    {{-- Blocs par marking_group --}}
    @php
        $markingGroupItems = $quote->items->groupBy('marking_group')->sortKeys();
        $zoneLabels = [
            'poitrine_gauche' => 'Poitrine gauche', 'poitrine_droite' => 'Poitrine droite',
            'poitrine_centre' => 'Poitrine centre', 'dos_centre' => 'Dos centre',
            'dos_haut' => 'Dos haut', 'manche_gauche' => 'Manche gauche',
            'manche_droite' => 'Manche droite', 'col' => 'Col',
        ];
    @endphp

    @foreach($markingGroupItems as $mgNum => $mgItems)
        @php
            $textileGroups = $mgItems->groupBy(fn($i) => $i->product_id . '-' . $i->product_color_id);
            if ($isGrouped) {
                $logos = $markings[$mgNum] ?? [];
                if (!empty($logos) && isset($logos['size'])) $logos = [$logos];
            } else {
                $logos = $markings;
            }
            $blocTotal = $mgItems->sum('line_total_ht');
            $blocTextile = $mgItems->sum(fn($i) => (float) $i->unit_price_ht * $i->quantity);
            $blocMarquage = $mgItems->sum(fn($i) => (float) $i->marking_price_ht * $i->quantity);
        @endphp

        @if($markingGroupItems->count() > 1)
            <div class="section-title">Bloc {{ $loop->iteration }}</div>
        @endif

        @foreach($textileGroups as $tgItems)
            @php $first = $tgItems->first(); @endphp
            <div class="product-header">
                <span class="name">{{ $first->product?->name }}</span>
                @if($first->color)
                    <span class="color">&mdash; {{ $first->color->name }}</span>
                @endif
            </div>

            <table class="lines" style="padding: 0 32px;">
                <thead>
                    <tr>
                        <th style="padding-left: 32px;">Description</th>
                        <th class="c" style="width: 50px;">Qte</th>
                        <th class="r" style="width: 70px;">P.U. HT</th>
                        <th class="r" style="width: 80px;">Total HT</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($tgItems->sortBy(fn($i) => $i->size?->sort_order ?? 0) as $item)
                        <tr>
                            <td style="padding-left: 32px;">Textile{{ $item->size ? ' ' . $item->size->size : '' }}</td>
                            <td class="c">{{ $item->quantity }}</td>
                            <td class="r">{{ number_format($item->unit_price_ht, 2, ',', ' ') }} &euro;</td>
                            <td class="r">{{ number_format($item->unit_price_ht * $item->quantity, 2, ',', ' ') }} &euro;</td>
                        </tr>
                        @if((float) $item->marking_price_ht > 0)
                            <tr class="marking">
                                <td class="label" style="padding-left: 40px;">
                                    Marquage{{ $item->size ? ' ' . $item->size->size : '' }}
                                    @if($item->technique) ({{ $item->technique->name }}) @endif
                                </td>
                                <td class="c">{{ $item->quantity }}</td>
                                <td class="r">{{ number_format($item->marking_price_ht, 2, ',', ' ') }} &euro;</td>
                                <td class="r">{{ number_format($item->marking_price_ht * $item->quantity, 2, ',', ' ') }} &euro;</td>
                            </tr>
                        @endif
                    @endforeach
                </tbody>
            </table>
        @endforeach

        {{-- Logos --}}
        @if(!empty($logos))
            <div class="logos-section">
                @foreach($logos as $li => $logo)
                    @php
                        $techName = !empty($logo['technique_id']) ? \App\Models\MarkingTechnique::find($logo['technique_id'])?->name : null;
                        $isName = ($logo['type'] ?? 'logo') === 'name';
                    @endphp
                    <div class="logo-line">
                        @if($isName)
                            Prenom/Pseudo &mdash; {{ ucfirst($logo['name_size'] ?? 'petit') }}, {{ $logo['name_lines'] ?? '1' }} ligne(s)
                        @else
                            Logo {{ $li + 1 }} &mdash; {{ $logo['size'] ?? 'A4' }},
                            {{ ($logo['colors'] ?? '1') === 'quadri' ? 'Quadri' : ($logo['colors'] ?? '1') . ' coul.' }},
                            {{ $zoneLabels[$logo['zone'] ?? ''] ?? 'Non defini' }}
                        @endif
                        @if($techName) &mdash; <span class="tech">{{ $techName }}</span> @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Sous-total bloc --}}
        <div class="bloc-subtotal">
            <span class="left">
                Textile : {{ number_format($blocTextile, 2, ',', ' ') }} &euro;
                @if($blocMarquage > 0)
                    + Marquage : {{ number_format($blocMarquage, 2, ',', ' ') }} &euro;
                @endif
            </span>
            <span class="right">{{ number_format($blocTotal, 2, ',', ' ') }} &euro; HT</span>
        </div>
    @endforeach

    {{-- Totaux --}}
    <div class="totals">
        <table>
            <tr>
                <td>Sous-total HT</td>
                <td class="r">{{ number_format($quote->total_ht, 2, ',', ' ') }} &euro;</td>
            </tr>
            <tr>
                <td>TVA (20%)</td>
                <td class="r">{{ number_format($quote->total_tva, 2, ',', ' ') }} &euro;</td>
            </tr>
            <tr class="total">
                <td>Total TTC</td>
                <td class="r">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</td>
            </tr>
        </table>
    </div>

    {{-- Conditions --}}
    <div style="padding: 16px 32px; font-size: 9px; color: #999; border-top: 1px solid #eee;">
        <p><strong>Conditions :</strong> Devis valable {{ $quote->expires_at ? $quote->daysRemaining() . ' jours' : '14 jours' }} a compter de la date d'edition.
        Reglement a la commande. Production lancee apres validation du BAT.</p>
        <p style="margin-top: 6px;">EIRL LEFEBVRE &mdash; LCS Marquage Textile &mdash; contact@marquage-textile.fr &mdash; 03 20 40 06 90</p>
    </div>

    <div class="footer">
        EIRL LEFEBVRE &mdash; LCS Marquage Textile &mdash; SIRET 794 260 954 00033 &mdash; TVA FR54 790260954<br>
        19 rue de la Resistance, 59155 Faches-Thumesnil &mdash; 03 20 40 06 90 &mdash; contact@marquage-textile.fr
    </div>
</body>
</html>
