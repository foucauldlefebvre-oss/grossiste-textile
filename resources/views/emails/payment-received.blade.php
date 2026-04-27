@extends('emails._layout')

@section('content')
    <h2>Paiement recu</h2>

    <p>Bonjour {{ $order->user?->name ?? 'cher client' }},</p>

    <p>Nous avons bien recu votre paiement pour la commande <strong>{{ $order->reference }}</strong>.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Reference</span>
            <span class="info-value">{{ $order->reference }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Montant recu</span>
            <span class="info-value" style="color: #059669;">{{ number_format($order->amount_paid, 2, ',', ' ') }} &euro;</span>
        </div>
        <div class="info-row">
            <span class="info-label">Statut</span>
            <span class="info-value">{{ $order->payment_status === 'paid' ? 'Integralement paye' : 'Acompte recu' }}</span>
        </div>
    </div>

    @if($order->has_marking)
        <p>Votre commande inclut du marquage : nous vous enverrons le Bon A Tirer (BAT) prochainement pour validation.</p>
    @else
        <p>Votre commande est en cours de preparation.</p>
    @endif

    <p style="text-align: center; margin-top: 24px;">
        <a href="{{ route('account.orders') }}" class="btn">Voir ma commande</a>
    </p>
@endsection
