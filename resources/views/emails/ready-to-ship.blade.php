@extends('emails._layout')

@section('content')
    @if($order->payment_status !== 'paid')
        <h2>Votre commande est prete — en attente du solde</h2>
    @else
        <h2>Votre commande est prete !</h2>
    @endif

    <p>Bonjour {{ $order->user?->name ?? 'cher client' }},</p>

    <p>Votre commande <strong>{{ $order->reference }}</strong> est prete a etre expediee.</p>

    @if($order->payment_status !== 'paid')
        @php $solde = (float) $order->total_ttc - (float) ($order->amount_paid ?? 0); @endphp
        <div class="info-box" style="background: #fef3c7; border-color: #fde68a;">
            <p style="margin: 0; color: #92400e; font-size: 14px;">
                <strong>Solde restant du : {{ number_format($solde, 2, ',', ' ') }} &euro; TTC</strong><br>
                Merci de proceder au reglement du solde pour que nous puissions expedier votre commande.
            </p>
            <p style="margin: 8px 0 0; color: #92400e; font-size: 13px;">
                Virement bancaire : IBAN FR76 1350 7000 7230 9723 2213 664 — BIC CCBPFRPPLIL<br>
                Reference a indiquer : {{ $order->reference }}
            </p>
        </div>
    @else
        <p>Vous recevrez un email avec le numero de suivi des l'expedition.</p>
    @endif

    <p style="text-align: center; margin-top: 24px;">
        <a href="{{ route('account.orders') }}" class="btn">Voir ma commande</a>
    </p>
@endsection
