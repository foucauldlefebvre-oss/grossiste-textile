@extends('emails._layout')

@section('content')
    <h2>Facture acompte</h2>

    <p>Bonjour {{ $order->user?->name ?? 'cher client' }},</p>

    <p>Veuillez trouver ci-joint la facture d'acompte pour votre commande <strong>{{ $order->reference }}</strong>.</p>

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
            <span class="info-label">Acompte recu</span>
            <span class="info-value" style="color: #059669;">{{ number_format($order->amount_paid, 2, ',', ' ') }} &euro;</span>
        </div>
        <div class="info-row" style="border-top: 1px solid #e5e7eb; padding-top: 8px; margin-top: 8px;">
            <span class="info-label" style="font-weight: 700;">Restant du</span>
            <span class="info-value" style="color: #dc2626;">{{ number_format($order->total_ttc - ($order->amount_paid ?? 0), 2, ',', ' ') }} &euro;</span>
        </div>
    </div>

    <p>Le solde sera a regler avant expedition de votre commande.</p>
@endsection
