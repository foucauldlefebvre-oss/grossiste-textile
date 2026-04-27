<div>
    {{-- Header --}}
    <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Bon a Tirer</h1>
                    <p class="text-blue-200 text-sm mt-1">Devis {{ $quote->reference }}</p>
                </div>
                <button wire:click="downloadPdf" class="flex items-center gap-2 px-4 py-2 bg-white/10 hover:bg-white/20 rounded-lg text-sm font-medium transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                    </svg>
                    Telecharger PDF
                </button>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        {{-- Already responded --}}
        @if($responded)
            <div class="rounded-xl p-6 text-center {{ $quote->bat_status === 'approved' ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }}">
                @if($quote->bat_status === 'approved')
                    <div class="w-16 h-16 mx-auto mb-4 bg-green-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-green-800 mb-1">BAT approuve</h2>
                    <p class="text-green-600 text-sm">Merci pour votre validation. Votre commande va etre lancee en production.</p>
                @else
                    <div class="w-16 h-16 mx-auto mb-4 bg-red-100 rounded-full flex items-center justify-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </div>
                    <h2 class="text-xl font-bold text-red-800 mb-1">BAT refuse</h2>
                    <p class="text-red-600 text-sm">Nous avons bien pris en compte votre refus. Nous vous recontacterons rapidement.</p>
                    @if($quote->bat_rejection_reason)
                        <p class="mt-3 text-sm text-red-700 bg-red-100 rounded-lg p-3 inline-block">
                            <strong>Motif :</strong> {{ $quote->bat_rejection_reason }}
                        </p>
                    @endif
                @endif
            </div>
        @endif

        {{-- Quote summary --}}
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b bg-gray-50 flex items-center justify-between">
                <h2 class="font-semibold">Detail du devis</h2>
                <span class="text-sm text-gray-500">{{ $quote->reference }}</span>
            </div>

            {{-- Items --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Produit</th>
                            <th class="px-4 py-3 text-left">Coloris</th>
                            <th class="px-4 py-3 text-left">Taille</th>
                            <th class="px-4 py-3 text-left">Technique</th>
                            <th class="px-4 py-3 text-left">Zone</th>
                            <th class="px-4 py-3 text-center">Qte</th>
                            <th class="px-4 py-3 text-right">Total HT</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @php
                            $zoneLabels = [
                                'poitrine_gauche' => 'Poitrine G',
                                'poitrine_droite' => 'Poitrine D',
                                'dos' => 'Dos',
                                'dos_haut' => 'Dos (haut)',
                                'manche_gauche' => 'Manche G',
                                'manche_droite' => 'Manche D',
                                'col' => 'Col',
                                'face' => 'Face',
                                'cote' => 'Cote',
                            ];
                        @endphp
                        @foreach($quote->items as $item)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="font-medium">{{ $item->product->name }}</span>
                                    <br><span class="text-gray-400 text-xs">{{ $item->product->reference }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->color)
                                        <span class="inline-flex items-center gap-1">
                                            <span class="w-3 h-3 rounded-full border border-gray-200" style="background-color: {{ $item->color->hex_code }}"></span>
                                            {{ $item->color->name }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">{{ $item->size?->size ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($item->technique)
                                        <span class="px-2 py-0.5 bg-blue-50 text-blue-700 text-xs rounded">{{ $item->technique->name }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($item->marking_zone)
                                        <span class="text-xs">{{ $zoneLabels[$item->marking_zone] ?? $item->marking_zone }}</span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center font-medium">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right font-semibold">{{ number_format($item->line_total_ht, 2, ',', ' ') }} &euro;</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Totals --}}
            <div class="px-6 py-4 border-t bg-gray-50">
                <div class="max-w-xs ml-auto space-y-1">
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>Sous-total HT</span>
                        <span>{{ number_format($quote->total_ht, 2, ',', ' ') }} &euro;</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-500">
                        <span>TVA (20%)</span>
                        <span>{{ number_format($quote->total_tva, 2, ',', ' ') }} &euro;</span>
                    </div>
                    <div class="flex justify-between font-bold text-base pt-2 border-t">
                        <span>Total TTC</span>
                        <span class="text-blue-600">{{ number_format($quote->total_ttc, 2, ',', ' ') }} &euro;</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approval actions (only if not yet responded) --}}
        @if(!$responded)
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h3 class="font-semibold mb-4">Votre decision</h3>

                @if($showRejectForm)
                    <div class="space-y-4">
                        <p class="text-sm text-gray-600">Veuillez indiquer le motif de votre refus (optionnel) :</p>
                        <textarea wire:model="rejectionReason" rows="3" placeholder="Motif du refus..."
                                  class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-red-500 focus:border-red-500"></textarea>
                        <div class="flex gap-3">
                            <button wire:click="reject"
                                    wire:loading.attr="disabled"
                                    class="px-6 py-3 bg-red-600 text-white font-semibold rounded-lg hover:bg-red-700 transition">
                                Confirmer le refus
                            </button>
                            <button wire:click="cancelReject"
                                    class="px-6 py-3 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition">
                                Annuler
                            </button>
                        </div>
                    </div>
                @else
                    <div class="flex flex-col sm:flex-row gap-3">
                        <button wire:click="approve"
                                wire:confirm="Confirmez-vous l'approbation de ce Bon a Tirer ?"
                                wire:loading.attr="disabled"
                                class="flex-1 px-6 py-3 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition text-center">
                            <span wire:loading.remove wire:target="approve">Approuver le BAT</span>
                            <span wire:loading wire:target="approve">Validation en cours...</span>
                        </button>
                        <button wire:click="showReject"
                                class="flex-1 px-6 py-3 border-2 border-red-300 text-red-600 font-semibold rounded-lg hover:bg-red-50 transition text-center">
                            Refuser le BAT
                        </button>
                    </div>
                    <p class="mt-3 text-xs text-gray-400 text-center">
                        En approuvant, vous confirmez que le contenu du devis est conforme a vos attentes. La production sera lancee.
                    </p>
                @endif
            </div>
        @endif

        {{-- Notes --}}
        @if($quote->notes)
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4 text-sm text-yellow-800">
                <strong>Notes :</strong> {{ $quote->notes }}
            </div>
        @endif
    </div>
</div>
