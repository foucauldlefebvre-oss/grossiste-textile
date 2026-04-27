<x-filament-panels::page>
    {{-- Stats --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600">{{ $this->getProductCount() }}</div>
                <div class="text-sm text-gray-500">Produits Toptex en base</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-3xl font-bold text-primary-600">{{ $this->getColorCount() }}</div>
                <div class="text-sm text-gray-500">Coloris</div>
                <div class="text-xs text-gray-400 mt-1">{{ $this->getBrands() }}</div>
            </div>
        </x-filament::section>

        <x-filament::section>
            <div class="text-center">
                <div class="text-sm text-gray-500 mb-1">API Toptex (AWS Cognito)</div>
                <div class="text-xs font-medium font-mono">{{ $this->getKeyStatus() }}</div>
                <div class="text-xs text-gray-400 mt-1 truncate">{{ $this->getApiUrl() }}</div>
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
                Dry Run (simulation)
            </x-filament::button>

            <x-filament::button
                wire:click="runImport"
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
                MAJ stocks/prix uniquement
            </x-filament::button>
        </div>

        {{-- Loading indicator --}}
        <div wire:loading wire:target="testConnection, runImport, runStockUpdate, runDryRun, runDumpStructure" class="mt-4">
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
