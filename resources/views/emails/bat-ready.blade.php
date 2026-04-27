@extends('emails._layout')

@section('content')
    <h2>Votre BAT est pret !</h2>

    <p>Bonjour {{ $quote->user?->name ?? 'cher client' }},</p>

    <p>Le Bon A Tirer pour votre devis <strong>{{ $quote->reference }}</strong> est pret pour validation.</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Reference devis</span>
            <span class="info-value">{{ $quote->reference }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total TTC</span>
            <span class="info-value">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
        </div>
    </div>

    <p>Veuillez consulter le BAT et l'approuver ou le rejeter via le lien ci-dessous.</p>

    @if($quote->bat_token)
        <p style="text-align: center; margin-top: 24px;">
            <a href="{{ route('bat.show', $quote->bat_token) }}" class="btn">Voir et valider le BAT</a>
        </p>
    @endif
@endsection
