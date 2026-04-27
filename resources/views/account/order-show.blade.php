<x-layouts.app title="Commande {{ $order->reference }}">

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8">
            @include('account._sidebar')

            <div class="flex-1">
                <div class="flex items-center gap-3 mb-6">
                    <a href="{{ route('account.orders') }}" class="text-gray-400 hover:text-gray-600 transition">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                    </a>
                    <h1 class="text-2xl font-bold">Commande {{ $order->reference }}</h1>
                </div>

                {{-- Order timeline --}}
                @php
                    $statusFlow = ['pending', 'confirmed', 'in_production', 'shipped', 'delivered'];
                    $sl = ['pending'=>'En attente','confirmed'=>'Confirmee','in_production'=>'En production','shipped'=>'Expediee','delivered'=>'Livree','cancelled'=>'Annulee','refunded'=>'Remboursee'];
                    $isCancelled = in_array($order->status, ['cancelled', 'refunded']);

                    // Déterminer l'étape réelle à partir des sous-statuts
                    $effectiveStatus = $order->status;
                    if ($effectiveStatus === 'confirmed') {
                        if ($order->status_production === 'done') {
                            $effectiveStatus = 'in_production'; // production terminée = prêt à expédier
                        } elseif ($order->status_prep === 'done' || $order->bat_status === 'approved') {
                            $effectiveStatus = 'in_production';
                        }
                    }
                    $currentIdx = array_search($effectiveStatus, $statusFlow);
                    if ($currentIdx === false) $currentIdx = -1;
                @endphp
                @if(!$isCancelled)
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="flex items-center justify-between overflow-x-auto">
                        @foreach($statusFlow as $i => $s)
                            <div class="flex items-center {{ $i > 0 ? 'flex-1' : '' }}">
                                @if($i > 0)
                                    <div class="flex-1 h-0.5 mx-1 {{ $i <= $currentIdx ? 'bg-green-500' : 'bg-gray-200' }}"></div>
                                @endif
                                <div class="flex flex-col items-center gap-1 flex-shrink-0">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold {{ $i < $currentIdx ? 'bg-green-500 text-white' : ($i === $currentIdx ? 'bg-bordeaux text-white' : 'bg-gray-100 text-gray-400') }}">
                                        @if($i < $currentIdx)
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                        @else
                                            {{ $i + 1 }}
                                        @endif
                                    </div>
                                    <span class="text-[10px] font-medium whitespace-nowrap {{ $i <= $currentIdx ? ($i === $currentIdx ? 'text-bordeaux' : 'text-green-600') : 'text-gray-400' }}">{{ $sl[$s] }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Status + meta --}}
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                        <div>
                            <span class="text-gray-500 block">Date</span>
                            <span class="font-medium">{{ $order->created_at->format('d/m/Y H:i') }}</span>
                        </div>
                        <div>
                            <span class="text-gray-500 block">Statut</span>
                            @if($isCancelled)
                                <span class="font-medium text-red-600">{{ $sl[$order->status] ?? $order->status }}</span>
                            @else
                                <span class="font-medium">{{ $sl[$order->status] ?? $order->status }}</span>
                            @endif
                        </div>
                        <div>
                            <span class="text-gray-500 block">Paiement</span>
                            <span class="font-medium {{ $order->payment_status === 'paid' ? 'text-green-600' : '' }}">
                                {{ $order->payment_status === 'paid' ? 'Paye' : 'En attente' }}
                            </span>
                        </div>
                        @if($order->tracking_number)
                            <div>
                                <span class="text-gray-500 block">Suivi</span>
                                <a href="{{ $order->tracking_url }}" target="_blank" class="inline-flex items-center gap-1 text-bordeaux hover:underline font-medium">
                                    {{ $order->tracking_number }}
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                                </a>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Alertes / Actions requises --}}
                @if($order->payment_status === 'pending' && !$order->stripe_payment_intent_id)
                    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
                        <div>
                            <p class="font-semibold text-amber-800 text-sm">En attente de virement</p>
                            <p class="text-xs text-amber-700 mt-0.5">Veuillez effectuer le virement bancaire pour que votre commande soit traitee.</p>
                        </div>
                    </div>
                @endif

                @if($order->has_marking)
                    @php
                        $markings = $order->quote?->markings ?? [];
                        $firstGroup = is_array($markings) ? (reset($markings) ?: []) : [];
                        if (!empty($firstGroup) && isset($firstGroup['size'])) $firstGroup = [$firstGroup];
                        $logoCount = count($firstGroup);
                        $uploadDir = 'orders/' . $order->reference;
                        $uploadedFiles = \Illuminate\Support\Facades\Storage::disk('public')->exists($uploadDir)
                            ? count(\Illuminate\Support\Facades\Storage::disk('public')->allFiles($uploadDir))
                            : 0;
                        $filesNeeded = $uploadedFiles < $logoCount;
                    @endphp

                    @if($filesNeeded)
                        <div class="bg-blue-50 border border-blue-200 rounded-xl p-4 mb-6">
                            <div class="flex items-start gap-3 mb-3">
                                <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                                <div>
                                    <p class="font-semibold text-blue-800 text-sm">En attente de fichiers ({{ $uploadedFiles }}/{{ $logoCount }})</p>
                                    <p class="text-xs text-blue-700 mt-0.5">Chargez vos fichiers de marquage pour que nous puissions preparer votre BAT.</p>
                                </div>
                            </div>
                            <livewire:order-file-upload :order="$order" />
                        </div>
                    @else
                        {{-- Fichiers déjà chargés --}}
                        @if($uploadedFiles > 0)
                            <div class="bg-green-50 border border-green-200 rounded-xl p-4 mb-6">
                                <div class="flex items-center gap-2 mb-2">
                                    <svg class="w-5 h-5 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                    <p class="font-semibold text-green-800 text-sm">Vos fichiers ont bien ete envoyes</p>
                                </div>
                                <p class="text-xs text-green-700">{{ $uploadedFiles }} fichier(s) charge(s). Notre equipe prepare votre BAT.</p>
                            </div>
                        @endif
                    @endif
                @endif

                {{-- Items --}}
                <div class="bg-white rounded-xl shadow-sm overflow-hidden mb-6">
                    <div class="px-6 py-4 border-b bg-gray-50">
                        <h2 class="font-semibold">Articles</h2>
                    </div>
                    <div class="divide-y">
                        @foreach($order->items as $item)
                            <div class="px-6 py-4 flex items-start gap-4">
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium">{{ $item->product->name ?? 'Produit' }}</p>
                                    <div class="flex flex-wrap gap-2 mt-1 text-xs text-gray-500">
                                        @if($item->color)
                                            <span class="flex items-center gap-1">
                                                <span class="w-3 h-3 rounded-full" style="background-color: {{ $item->color->hex_code }}"></span>
                                                {{ $item->color->name }}
                                            </span>
                                        @endif
                                        @if($item->size)
                                            <span>Taille {{ $item->size->size }}</span>
                                        @endif
                                        @if($item->technique)
                                            <span class="px-2 py-0.5 bg-bordeaux-50 text-bordeaux rounded">{{ $item->technique->name }}</span>
                                        @endif
                                    </div>
                                </div>
                                <div class="text-sm text-gray-500">x {{ $item->quantity }}</div>
                                <div class="text-right w-28">
                                    <p class="font-semibold">{{ number_format($item->line_total_ht, 2, ',', ' ') }} &euro;</p>
                                    <p class="text-xs text-gray-400">HT</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Totals --}}
                <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                    <div class="max-w-sm ml-auto space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Sous-total HT</span>
                            <span>{{ number_format($order->subtotal_ht, 2, ',', ' ') }} &euro;</span>
                        </div>
                        @if($order->shipping_ht > 0)
                            <div class="flex justify-between">
                                <span class="text-gray-500">Livraison HT</span>
                                <span>{{ number_format($order->shipping_ht, 2, ',', ' ') }} &euro;</span>
                            </div>
                        @endif
                        <div class="flex justify-between">
                            <span class="text-gray-500">TVA (20%)</span>
                            <span>{{ number_format($order->total_tva, 2, ',', ' ') }} &euro;</span>
                        </div>
                        <div class="border-t pt-2 flex justify-between font-bold text-base">
                            <span>Total TTC</span>
                            <span class="text-bordeaux">{{ number_format($order->total_ttc, 2, ',', ' ') }} &euro;</span>
                        </div>
                    </div>
                </div>

                {{-- BAT section (commandes avec marquage) --}}
                @if($order->has_marking && $order->bat_status !== 'none')
                    <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
                        <h2 class="font-semibold mb-3">Bon A Tirer</h2>

                        @if($order->bat_status === 'sent')
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg mb-3">
                                <p class="text-sm text-amber-800 font-medium">Un BAT est disponible pour validation.</p>
                            </div>
                            <a href="{{ $order->bat_review_url }}"
                               class="inline-flex items-center gap-2 px-4 py-2.5 bg-amber-500 text-white font-semibold rounded-lg hover:bg-amber-600 transition text-sm">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                </svg>
                                Consulter et valider le BAT
                            </a>

                        @elseif($order->bat_status === 'approved')
                            <div class="p-3 bg-green-50 border border-green-200 rounded-lg flex items-center gap-2">
                                <svg class="w-5 h-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                </svg>
                                <span class="text-sm text-green-800 font-medium">BAT approuve le {{ $order->bat_client_decided_at?->format('d/m/Y') }}</span>
                            </div>

                        @elseif($order->bat_status === 'revision_requested')
                            <div class="p-3 bg-amber-50 border border-amber-200 rounded-lg">
                                <p class="text-sm text-amber-800 font-medium mb-1">Modification demandee</p>
                                <p class="text-xs text-amber-600">{{ $order->bat_client_comment }}</p>
                                <p class="text-xs text-amber-500 mt-1">Un nouveau BAT vous sera envoye prochainement.</p>
                            </div>

                        @elseif($order->bat_status === 'pending')
                            <div class="p-3 bg-gray-50 border border-gray-200 rounded-lg">
                                <p class="text-sm text-gray-500">Le BAT est en cours de preparation. Vous serez notifie par email.</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Invoice + Actions --}}
                @if($order->invoice || $order->tracking_url)
                    <div class="bg-white rounded-xl shadow-sm p-6">
                        <h2 class="font-semibold mb-3">Documents & suivi</h2>
                        <div class="flex flex-wrap gap-3">
                            @if($order->invoice)
                                <a href="{{ route('account.orders.invoice', $order->reference) }}"
                                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition">
                                    <svg class="w-4 h-4 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Facture {{ $order->invoice->number }}
                                </a>
                            @endif
                            @if($order->tracking_url)
                                <a href="{{ $order->tracking_url }}" target="_blank"
                                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-bordeaux-50 border border-bordeaux-200 rounded-lg text-sm font-medium text-bordeaux hover:bg-bordeaux-100 transition">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    Suivre mon colis
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

</x-layouts.app>
