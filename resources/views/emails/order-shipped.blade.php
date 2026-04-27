@extends('emails._layout')

@section('content')
    <h2>Votre commande est en route !</h2>

    <p>Bonjour {{ $order->user?->name ?? 'cher client' }},</p>

    <p>Votre commande <strong>{{ $order->reference }}</strong> a ete expediee.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Transporteur</span>
            <span class="info-value">{{ $order->carrier ?? 'Non precise' }}</span>
        </div>
        @if($order->tracking_number)
            <div class="info-row">
                <span class="info-label">N&deg; de suivi</span>
                <span class="info-value">{{ $order->tracking_number }}</span>
            </div>
        @endif
        <div class="info-row">
            <span class="info-label">Date d'expedition</span>
            <span class="info-value">{{ $order->shipped_at?->format('d/m/Y') ?? now()->format('d/m/Y') }}</span>
        </div>
    </div>

    @if($order->tracking_url)
        <p style="text-align: center; margin-top: 24px;">
            <a href="{{ $order->tracking_url }}" class="btn">Suivre mon colis</a>
        </p>
    @endif

    <p>Vous recevrez une notification lorsque votre commande sera livree.</p>
@endsection
