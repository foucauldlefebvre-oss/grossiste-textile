@extends('emails._layout')

@section('content')
    <h2>BAT valide par le client</h2>

    <p>Le client a approuve le BAT pour la commande <strong>{{ $order->reference }}</strong>.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Client</span>
            <span class="info-value">{{ $order->user?->name ?? '?' }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Commande</span>
            <span class="info-value">{{ $order->reference }}</span>
        </div>
        @if($order->bat_client_comment)
            <div class="info-row">
                <span class="info-label">Commentaire</span>
                <span class="info-value">{{ $order->bat_client_comment }}</span>
            </div>
        @endif
    </div>

    <p>La production peut etre lancee.</p>

    <p style="text-align: center; margin-top: 24px;">
        <a href="{{ url('/admin/orders/' . $order->id) }}" class="btn">Voir la commande</a>
    </p>
@endsection
