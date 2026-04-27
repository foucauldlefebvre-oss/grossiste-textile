@extends('emails._layout')

@section('content')
    <h2>En attente de votre virement</h2>

    <p>Bonjour {{ $order->user?->name ?? 'cher client' }},</p>

    <p>Votre commande <strong>{{ $order->reference }}</strong> a bien ete enregistree. Pour la valider, merci d'effectuer le virement bancaire aux coordonnees suivantes :</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Beneficiaire</span>
            <span class="info-value">EIRL LEFEBVRE</span>
        </div>
        <div class="info-row">
            <span class="info-label">IBAN</span>
            <span class="info-value" style="font-family: monospace;">FR76 1350 7000 7230 9723 2213 664</span>
        </div>
        <div class="info-row">
            <span class="info-label">BIC</span>
            <span class="info-value" style="font-family: monospace;">CCBPFRPPLIL</span>
        </div>
        <div class="info-row">
            <span class="info-label">Reference a indiquer</span>
            <span class="info-value" style="color: #6b1d2a;">{{ $order->reference }}</span>
        </div>
        @php
            $isDeposit = str_contains($order->customer_notes ?? '', 'Acompte');
            $amountDue = $isDeposit ? round((float) $order->total_ttc / 2, 2) : (float) $order->total_ttc;
        @endphp
        <div class="info-row" style="border-top: 1px solid #e5e7eb; padding-top: 8px; margin-top: 8px;">
            <span class="info-label" style="font-weight: 700;">Montant a virer</span>
            <span class="info-value" style="color: #6b1d2a; font-size: 16px;">{{ number_format($amountDue, 2, ',', ' ') }} &euro; TTC</span>
        </div>
        @if($isDeposit)
            <div class="info-row" style="margin-top: 4px;">
                <span class="info-label">Detail</span>
                <span class="info-value">Acompte 50% — Solde de {{ number_format($amountDue, 2, ',', ' ') }} &euro; a regler avant expedition</span>
            </div>
        @endif
    </div>

    @if($isDeposit)
        <p>Il s'agit d'un acompte de 50%. Le solde restant vous sera demande avant l'expedition.</p>
    @else
        <p>Votre commande sera traitee des reception du virement.</p>
    @endif

    <p style="text-align: center; margin-top: 24px;">
        <a href="{{ route('account.orders') }}" class="btn">Voir ma commande</a>
    </p>
@endsection
