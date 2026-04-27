<x-filament-panels::page>
<div class="space-y-6">

  {{-- BLOC 1 : Informations devis --}}
  <x-filament::section heading="Informations du devis">
    <dl class="grid grid-cols-2 gap-4 text-sm">
      <div>
        <dt class="text-gray-500">Référence</dt>
        <dd class="font-medium">{{ $this->record->reference }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Statut</dt>
        <dd>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
            {{ match($this->record->status) {
              'draft'    => 'bg-gray-100 text-gray-700',
              'sent'     => 'bg-blue-100 text-blue-700',
              'accepted' => 'bg-green-100 text-green-700',
              'refused'  => 'bg-red-100 text-red-700',
              'expired'  => 'bg-yellow-100 text-yellow-700',
              default    => 'bg-gray-100 text-gray-700'
            } }}">
            {{ ucfirst($this->record->status) }}
          </span>
        </dd>
      </div>
      <div>
        <dt class="text-gray-500">Client</dt>
        <dd class="font-medium">
          {{ $this->record->user?->name ?? 'Client non connecté' }}
        </dd>
      </div>
      <div>
        <dt class="text-gray-500">Créé le</dt>
        <dd>{{ $this->record->created_at->format('d/m/Y') }}</dd>
      </div>
      <div>
        <dt class="text-gray-500">Total HT</dt>
        <dd class="font-medium">
          {{ number_format($this->record->total_ht, 2, ',', ' ') }} €
        </dd>
      </div>
      <div>
        <dt class="text-gray-500">Total TTC</dt>
        <dd class="font-medium text-lg">
          {{ number_format($this->record->total_ttc, 2, ',', ' ') }} €
        </dd>
      </div>
      @if($this->record->notes)
      <div class="col-span-2">
        <dt class="text-gray-500">Notes</dt>
        <dd>{{ $this->record->notes }}</dd>
      </div>
      @endif
    </dl>
  </x-filament::section>

  {{-- BLOC 2 : Produits et marquage --}}
  @if($this->record->items->isNotEmpty())
  <x-filament::section heading="Produits commandés">
    <div class="space-y-3">
      @foreach($this->record->items as $item)
      <div class="flex items-center gap-4 p-3 rounded-lg bg-gray-50 dark:bg-gray-800 text-sm">

        {{-- Couleur textile --}}
        @if($item->color?->hex_code)
        <div class="flex items-center gap-2">
          <div class="w-6 h-6 rounded-full border border-gray-200 flex-shrink-0"
               style="background-color: {{ $item->color->hex_code }}"></div>
          <span class="text-gray-600">{{ $item->color->name }}</span>
        </div>
        @endif

        <div class="flex-1">
          <div class="font-medium">
            {{ $item->product?->name ?? 'Produit #'.$item->product_id }}
          </div>
          <div class="text-gray-500 text-xs mt-0.5">
            @if($item->technique)
              {{ $item->technique->name }} &middot;
            @endif
            Qté : {{ $item->quantity }}
          </div>
        </div>

        <div class="text-right">
          <div class="font-medium">
            {{ number_format($item->line_total_ht, 2, ',', ' ') }} € HT
          </div>
        </div>
      </div>
      @endforeach
    </div>
  </x-filament::section>
  @endif

  {{-- BLOC 3 : Marquage --}}
  @if($this->record->markings)
  <x-filament::section heading="Paramètres de marquage">
    <dl class="grid grid-cols-3 gap-4 text-sm">
      <div>
        <dt class="text-gray-500">Format</dt>
        <dd class="font-medium">
          {{ data_get($this->record->markings, '0.size', '—') }}
        </dd>
      </div>
      <div>
        <dt class="text-gray-500">Position</dt>
        <dd class="font-medium">
          @php
            $zoneMap = [
              'poitrine_gauche' => 'Poitrine gauche',
              'poitrine_droite' => 'Poitrine droite',
              'poitrine_centre' => 'Poitrine centré',
              'dos_centre'      => 'Dos centré',
              'dos_haut'        => 'Dos haut',
              'manche_gauche'   => 'Manche gauche',
              'manche_droite'   => 'Manche droite',
              'col'             => 'Col',
            ];
            $zone = data_get($this->record->markings, '0.zone', '');
          @endphp
          {{ $zoneMap[$zone] ?? 'Non défini' }}
        </dd>
      </div>
      <div>
        <dt class="text-gray-500">Nb couleurs logo</dt>
        <dd class="font-medium">
          {{ data_get($this->record->markings, '0.colors', '—') }}
        </dd>
      </div>
    </dl>
  </x-filament::section>
  @endif

  {{-- BLOC 4 : Statut BAT --}}
  @if($this->record->bat_token)
  <x-filament::section heading="Statut du BAT">
    <dl class="grid grid-cols-2 gap-4 text-sm">
      <div>
        <dt class="text-gray-500">Statut</dt>
        <dd>
          <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
            {{ match($this->record->bat_status) {
              'pending'            => 'bg-gray-100 text-gray-700',
              'sent'               => 'bg-blue-100 text-blue-700',
              'approved'           => 'bg-green-100 text-green-700',
              'rejected'           => 'bg-red-100 text-red-700',
              'revision_requested' => 'bg-yellow-100 text-yellow-700',
              default              => 'bg-gray-100 text-gray-700',
            } }}">
            {{ match($this->record->bat_status) {
              'pending'            => 'En attente',
              'sent'               => 'Envoyé',
              'approved'           => 'Approuvé',
              'rejected'           => 'Refusé',
              'revision_requested' => 'Révision demandée',
              default              => $this->record->bat_status,
            } }}
          </span>
        </dd>
      </div>
      <div>
        <dt class="text-gray-500">Envoyé le</dt>
        <dd>{{ $this->record->bat_sent_at?->format('d/m/Y à H:i') ?? '—' }}</dd>
      </div>
      @if($this->record->bat_decided_at)
      <div>
        <dt class="text-gray-500">Décision le</dt>
        <dd>{{ $this->record->bat_decided_at->format('d/m/Y à H:i') }}</dd>
      </div>
      @endif
      <div>
        <dt class="text-gray-500">Lien client</dt>
        <dd>
          <a href="/bat/{{ $this->record->bat_token }}"
             target="_blank"
             class="text-primary-600 hover:underline text-xs">
            Voir la page BAT →
          </a>
        </dd>
      </div>
      @if($this->record->bat_client_comment)
      <div class="col-span-2">
        <dt class="text-gray-500">Commentaire client</dt>
        <dd class="mt-1 p-2 bg-yellow-50 dark:bg-yellow-900/20 rounded text-yellow-800 dark:text-yellow-200">
          {{ $this->record->bat_client_comment }}
        </dd>
      </div>
      @endif
      @if($this->record->bat_rejection_reason)
      <div class="col-span-2">
        <dt class="text-gray-500">Raison du refus</dt>
        <dd class="mt-1 p-2 bg-red-50 dark:bg-red-900/20 rounded text-red-800 dark:text-red-200">
          {{ $this->record->bat_rejection_reason }}
        </dd>
      </div>
      @endif
    </dl>
  </x-filament::section>
  @endif

</div>
</x-filament-panels::page>
