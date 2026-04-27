@extends('emails._layout')

@section('content')
    <h2>Commande confirmee !</h2>

    <p>Bonjour {{ $order->user?->name ?? 'cher client' }},</p>

    <p>Votre commande <strong>{{ $order->reference }}</strong> a bien ete enregistree et votre paiement confirme.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Reference</span>
            <span class="info-value">{{ $order->reference }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Sous-total HT</span>
            <span class="info-value">{{ number_format($order->subtotal_ht, 2, ',', ' ') }} &euro;</span>
        </div>
        @if($order->shipping_ht > 0)
            <div class="info-row">
                <span class="info-label">Livraison HT</span>
                <span class="info-value">{{ number_format($order->shipping_ht, 2, ',', ' ') }} &euro;</span>
            </div>
        @endif
        <div class="info-row">
            <span class="info-label">TVA (20%)</span>
            <span class="info-value">{{ number_format($order->total_tva, 2, ',', ' ') }} &euro;</span>
        </div>
        <div class="info-row" style="border-top: 1px solid #e5e7eb; padding-top: 8px; margin-top: 8px;">
            <span class="info-label" style="font-weight: 700; color: #111827;">Total TTC</span>
            <span class="info-value" style="color: #6b1d2a;">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</span>
        </div>
    </div>

    <p>Nous preparons votre commande et vous tiendrons informe de son avancement.</p>

    <p style="text-align: center; margin-top: 24px;">
        <a href="{{ route('account.orders.show', $order->reference) }}" class="btn">Voir ma commande</a>
    </p>
@endsection
