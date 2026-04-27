@extends('emails._layout')

@section('content')
    <h2>Bienvenue !</h2>

    <p>Bonjour {{ $user->name }},</p>

    <p>Votre compte sur <strong>Marquage Textile</strong> a bien ete cree.</p>

    <p>Vous pouvez desormais :</p>
    <ul style="padding-left: 20px; margin: 16px 0; line-height: 2; font-size: 15px; color: #374151;">
        <li>Parcourir notre catalogue de textiles</li>
        <li>Demander des devis personnalises</li>
        <li>Suivre vos commandes en temps reel</li>
        <li>Gerer vos adresses de livraison</li>
    </ul>

    <p style="text-align: center; margin-top: 24px;">
        <a href="{{ route('catalogue.index') }}" class="btn">Decouvrir le catalogue</a>
    </p>
@endsection
