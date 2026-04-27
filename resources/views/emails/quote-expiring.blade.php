@extends('emails._layout')

@section('content')
    <h2>Votre devis expire bientot</h2>

    <p>Bonjour {{ $quote->user?->name ?? 'cher client' }},</p>

    <p>Votre devis <strong>{{ $quote->reference }}</strong> expire dans <strong>{{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }}</strong> (le {{ $quote->expires_at->format('d/m/Y') }}).</p>

    <div class="info-box">
        <div class="info-row">
            <span class="info-label">Reference</span>
            <span class="info-value">{{ $quote->reference }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Total TTC</span>
            <span class="info-value" style="color: #6b1d2a;">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
        </div>
    </div>

    <p>Passe ce delai, les tarifs devront etre reconfirmes. N'hesitez pas a valider votre devis en cliquant ci-dessous :</p>

    <p style="text-align: center; margin-top: 24px;">
        <a href="{{ route('mon-devis') }}" class="btn">Valider mon devis</a>
    </p>
@endsection
