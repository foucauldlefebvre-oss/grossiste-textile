<x-filament-panels::page>
    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600">{{ $this->getProductCount() }}</div>
                <div class="text-sm text-gray-500">Produits SOL'S / Solo en base</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 mb-1">Authentification</div>
                <div class="text-xs font-medium font-mono">{{ $this->getAuthStatus() }}</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 mb-1">API Gateway</div>
                <div class="text-xs text-gray-400 mt-1 truncate">{{ $this->getApiUrl() }}</div>
            </div>
        </x-filament::section>
    </div>

    {{-- API info --}}
    <x-filament::section>
        <div class="text-xs text-gray-500 space-y-1">
            <div><strong>Solo Paris — Gravitee.io API (OAuth2 Client Credentials)</strong></div>
            <div>APIs : Product Catalogue Model, Product Catalogue SKU, Customer Prices, StockItems</div>
            <div>Entrepôts : 001 = France, 201 = Pologne</div>
            <div>Token URL : <span class="font-mono">{{ $this->getTokenUrl() }}</span></div>
        </div>
    </x-filament::section>

    {{-- Actions --}}
    <x-filament::section heading="Actions">
        <div class="flex flex-wrap gap-3">
            <x-filament::button
                wire:click="testConnection"
                wire:loading.attr="disabled"
                color="gray"
                icon="heroicon-o-signal"
            >
                Tester OAuth2
            </x-filament::button>

            <x-filament::button
                wire:click="runDumpStructure"
                wire:loading.attr="disabled"
                color="gray"
                icon="heroicon-o-code-bracket"
            >
                Voir structure JSON
            </x-filament::button>

            <x-filament::button
                wire:click="runDryRun"
                wire:loading.attr="disabled"
                color="warning"
                icon="heroicon-o-eye"
            >
                Dry Run (50 produits)
            </x-filament::button>

            <x-filament::button
                wire:click="runStockUpdate"
                wire:loading.attr="disabled"
                color="success"
                icon="heroicon-o-arrow-path"
            >
                MAJ stocks
            </x-filament::button>

            <x-filament::button
                wire:click="runPriceUpdate"
                wire:loading.attr="disabled"
                color="success"
                icon="heroicon-o-currency-euro"
            >
                MAJ prix
            </x-filament::button>

            <x-filament::button
                wire:click="runFullImport"
                wire:loading.attr="disabled"
                color="primary"
                icon="heroicon-o-arrow-down-tray"
            >
                Import complet
            </x-filament::button>
        </div>

        {{-- Loading indicator --}}
        <div wire:loading wire:target="testConnection, runDumpStructure, runDryRun, runStockUpdate, runPriceUpdate, runFullImport" class="mt-4">
            <div class="flex items-center gap-2 text-primary-600">
                <x-filament::loading-indicator class="h-5 w-5" />
                <span>Opération en cours... Veuillez patienter.</span>
            </div>
        </div>
    </x-filament::section>

    {{-- Output --}}
    @if ($output)
        <x-filament::section heading="Résultat">
            <pre class="bg-gray-900 text-green-400 p-4 rounded-lg text-sm font-mono overflow-x-auto max-h-[500px] overflow-y-auto whitespace-pre-wrap">{{ $output }}</pre>
        </x-filament::section>
    @endif
</x-filament-panels::page>
