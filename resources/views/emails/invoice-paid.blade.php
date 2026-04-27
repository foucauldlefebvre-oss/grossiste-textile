@extends('emails._layout')

@section('content')
    <h2>Facture soldee</h2>

    <p>Bonjour {{ $order->user?->name ?? 'cher client' }},</p>

    <p>Votre commande <strong>{{ $order->reference }}</strong> est integralement payee. Veuillez trouver ci-joint votre facture soldee.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Facture n&deg;</span>
            <span class="info-value">{{ $invoice->number }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total TTC</span>
            <span class="info-value">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut</span>
            <span class="info-value" style="color: #059669;">Soldee</span>
        </div>
    </div>

    <p>Merci pour votre confiance.</p>

    <p style="text-align: center; margin-top: 24px;">
        <a href="{{ route('account.orders') }}" class="btn">Voir ma commande</a>
    </p>
@endsection
