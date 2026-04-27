<x-filament-panels::page>
    <form wire:submit.prevent="">
        {{ $this->form }}
    </form>

    @php
        $stats = $this->getStats();
        $topProducts = $this->getTopProducts();
        $topTechniques = $this->getTopTechniques();
        $margin = $this->getMarginData();
        $topPages = $this->getTopPages();
    @endphp

    {{-- KPIs --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600">{{ number_format($stats['visits']) }}</div>
                <div class="text-sm text-gray-500">Visites</div>
                <div class="text-xs text-gray-400">{{ number_format($stats['unique_visitors']) }} visiteurs uniques</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-warning-600">{{ $stats['new_clients'] }}</div>
                <div class="text-sm text-gray-500">Nouveaux clients</div>
                <div class="text-xs text-gray-400">{{ $stats['total_clients'] }} au total</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-info-600">{{ $stats['quotes_created'] }}</div>
                <div class="text-sm text-gray-500">Devis crees</div>
                <div class="text-xs text-gray-400">{{ $stats['quotes_accepted'] }} acceptes</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-success-600">{{ $stats['orders_created'] }}</div>
                <div class="text-sm text-gray-500">Commandes</div>
                <div class="text-xs text-gray-400">{{ $stats['orders_paid'] }} payees</div>
            </div>
        </x-filament::section>
    </div>

    {{-- CA et Marge --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-filament::section heading="Chiffre d'affaires">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">CA HT</span>
                    <span class="text-lg font-bold">{{ number_format($stats['revenue_ht'], 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">CA TTC</span>
                    <span class="text-lg font-bold">{{ number_format($stats['revenue_ttc'], 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Factures emises</span>
                    <span class="text-lg font-bold">{{ $stats['invoices'] }}</span>
                </div>
            </div>
        </x-filament::section>

        <x-filament::section heading="Marge textile">
            <div class="space-y-3">
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Vente HT (textile + marquage)</span>
                    <span class="font-medium">{{ number_format($margin['vente_ht'], 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-500">Achat textile HT</span>
                    <span class="font-medium text-red-600">- {{ number_format($margin['achat_textile_ht'], 2, ',', ' ') }} &euro;</span>
                </div>
                <div class="flex justify-between items-center border-t pt-2">
                    <span class="text-sm font-medium">Marge brute textile</span>
                    <span class="text-lg font-bold text-success-600">{{ number_format($margin['marge_textile_ht'], 2, ',', ' ') }} &euro; ({{ $margin['marge_pct'] }}%)</span>
                </div>
                <p class="text-xs text-gray-400 italic">Hors couts de marquage (a configurer)</p>
            </div>
        </x-filament::section>
    </div>

    {{-- Produits et Techniques --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <x-filament::section heading="Produits les plus commandes">
            @if(empty($topProducts))
                <p class="text-sm text-gray-400 text-center py-4">Aucune donnee sur cette periode</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 text-gray-500 font-medium">Produit</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Qte</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Cmd</th>
                                <th class="text-right py-2 text-gray-500 font-medium">CA HT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topProducts as $p)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2">
                                        <div class="font-medium">{{ $p->name }}</div>
                                        <div class="text-xs text-gray-400">{{ $p->reference }}</div>
                                    </td>
                                    <td class="text-right py-2">{{ number_format($p->total_qty) }}</td>
                                    <td class="text-right py-2">{{ $p->nb_orders }}</td>
                                    <td class="text-right py-2 font-medium">{{ number_format($p->total_ht, 0, ',', ' ') }} &euro;</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>

        <x-filament::section heading="Techniques de marquage">
            @if(empty($topTechniques))
                <p class="text-sm text-gray-400 text-center py-4">Aucune donnee sur cette periode</p>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left py-2 text-gray-500 font-medium">Technique</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Lignes</th>
                                <th class="text-right py-2 text-gray-500 font-medium">Qte</th>
                                <th class="text-right py-2 text-gray-500 font-medium">CA marquage HT</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topTechniques as $t)
                                <tr class="border-b border-gray-100">
                                    <td class="py-2 font-medium">{{ $t->name }}</td>
                                    <td class="text-right py-2">{{ $t->nb_lines }}</td>
                                    <td class="text-right py-2">{{ number_format($t->total_qty) }}</td>
                                    <td class="text-right py-2 font-medium">{{ number_format($t->total_marking_ht, 0, ',', ' ') }} &euro;</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </x-filament::section>
    </div>

    {{-- Pages les plus visitees --}}
    <x-filament::section heading="Pages les plus visitees">
        @if(empty($topPages))
            <p class="text-sm text-gray-400 text-center py-4">Aucune donnee sur cette periode</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-2 text-gray-500 font-medium">Page</th>
                            <th class="text-right py-2 text-gray-500 font-medium">Vues</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($topPages as $page)
                            <tr class="border-b border-gray-100">
                                <td class="py-2 font-mono text-xs">{{ $page->path }}</td>
                                <td class="text-right py-2 font-medium">{{ number_format($page->views) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </x-filament::section>
</x-filament-panels::page>
