@extends('emails._layout')

@section('content')
    <h2>Nouvelle commande en attente de virement</h2>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Reference</span>
            <span class="info-value">{{ $order->reference }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Client</span>
            <span class="info-value">{{ $order->user?->name ?? '?' }} ({{ $order->user?->email }})</span>
        </div>
        @if($order->user?->company)
            <div class="info-row">
                <span class="info-label">Societe</span>
                <span class="info-value">{{ $order->user->company }}</span>
            </div>
        @endif
        <div class="info-row">
            <span class="info-label">Total TTC</span>
            <span class="info-value" style="color: #6b1d2a;">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</span>
        </div>
        <div class="info-row">
            <span class="info-label">Marquage</span>
            <span class="info-value">{{ $order->has_marking ? 'Oui' : 'Non' }}</span>
        </div>
    </div>

    <p style="text-align: center; margin-top: 24px;">
        <a href="{{ url('/admin/orders/' . $order->id) }}" class="btn">Voir dans le back-office</a>
    </p>
@endsection
