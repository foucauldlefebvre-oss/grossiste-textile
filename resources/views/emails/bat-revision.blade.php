@extends('emails._layout')

@section('content')
    <h2>Nouveau BAT disponible</h2>

    <p>Bonjour {{ $order->user?->name ?? 'cher client' }},</p>

    <p>Suite a votre demande de modification, un nouveau Bon A Tirer est disponible pour votre commande <strong>{{ $order->reference }}</strong>.</p>

    <p>Veuillez le consulter et l'approuver ou demander une nouvelle modification via le lien ci-dessous :</p>

    @if($order->bat_token)
        <p style="text-align: center; margin-top: 24px;">
            <a href="{{ route('bat.show', $order->bat_token) }}" class="btn">Voir et valider le BAT</a>
        </p>
    @endif
@endsection
