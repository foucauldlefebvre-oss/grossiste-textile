<x-filament-panels::page>
    {{-- Tabs --}}
    <div class="flex gap-1 border-b border-gray-200 dark:border-gray-700 mb-4">
        @foreach ($this->getSupplierTabs() as $key => $tab)
            <button
                wire:click="switchTab('{{ $key }}')"
                class="px-4 py-2.5 text-sm font-medium rounded-t-lg transition
                    {{ $activeTab === $key
                        ? 'bg-primary-50 text-primary-700 border-b-2 border-primary-600 dark:bg-primary-900/20 dark:text-primary-400'
                        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 dark:text-gray-400 dark:hover:bg-gray-800' }}"
            >
                {{ $tab['label'] }}
                <span class="ml-1 text-xs bg-gray-100 dark:bg-gray-700 px-1.5 py-0.5 rounded-full">
                    {{ $this->getProductCount($key) }}
                </span>
            </button>
        @endforeach
    </div>

    @php $config = $this->getSupplierTabs()[$activeTab]; @endphp

    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600">{{ $this->getProductCount($activeTab) }}</div>
                <div class="text-sm text-gray-500">Produits en base</div>
                <div class="text-xs text-gray-400 mt-1">{{ $this->getBrands($activeTab) }}</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600">{{ $this->getColorCount($activeTab) }}</div>
                <div class="text-sm text-gray-500">Coloris</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 mb-1">API {{ $config['label'] }} ({{ $config['auth_type'] }})</div>
                <div class="text-xs font-medium font-mono">{{ $this->getAuthInfo($activeTab) }}</div>
                <div class="text-xs text-gray-400 mt-1 truncate">{{ $this->getApiUrl($activeTab) }}</div>
            </div>
        </x-filament::section>
    </div>

    {{-- Actions --}}
    <x-filament::section heading="Actions">
        <div class="flex flex-wrap gap-3">
            <x-filament::button
                wire:click="testConnection"
                wire:loading.attr="disabled"
                color="gray"
                icon="heroicon-o-signal"
            >
                Tester la connexion
            </x-filament::button>

            @if ($config['has_dump'])
                <x-filament::button
                    wire:click="runDumpStructure"
                    wire:loading.attr="disabled"
                    color="gray"
                    icon="heroicon-o-code-bracket"
                >
                    Voir structure JSON
                </x-filament::button>
            @endif

            <x-filament::button
                wire:click="runDryRun"
                wire:loading.attr="disabled"
                color="warning"
                icon="heroicon-o-eye"
            >
                Dry Run (simulation)
            </x-filament::button>

            <x-filament::button
                wire:click="runFullImport"
                wire:loading.attr="disabled"
                color="primary"
                icon="heroicon-o-arrow-down-tray"
            >
                Import complet
            </x-filament::button>

            <x-filament::button
                wire:click="runStockUpdate"
                wire:loading.attr="disabled"
                color="success"
                icon="heroicon-o-arrow-path"
            >
                MAJ stocks
            </x-filament::button>

            @if ($config['has_prices'])
                <x-filament::button
                    wire:click="runPriceUpdate"
                    wire:loading.attr="disabled"
                    color="success"
                    icon="heroicon-o-currency-euro"
                >
                    MAJ prix
                </x-filament::button>
            @endif
        </div>

        {{-- Loading --}}
        <div wire:loading wire:target="testConnection, runFullImport, runStockUpdate, runPriceUpdate, runDryRun, runDumpStructure" class="mt-4">
            <div class="flex items-center gap-2 text-primary-600">
                <x-filament::loading-indicator class="h-5 w-5" />
                <span>Operation en cours... Veuillez patienter.</span>
            </div>
        </div>
    </x-filament::section>

    {{-- Output --}}
    @if ($output)
        <x-filament::section heading="Resultat">
            <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm font-mono overflow-x-auto max-h-[500px] overflow-y-auto whitespace-pre-wrap">{{ $output }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
