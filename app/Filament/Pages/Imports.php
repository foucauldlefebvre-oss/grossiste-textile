<?php

namespace App\Filament\Pages;

use App\Models\Product;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class Imports extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Imports fournisseurs';

    protected static ?string $title = 'Imports fournisseurs';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.imports';

    public string $activeTab = 'toptex';

    public string $output = '';

    public bool $isRunning = false;

    // ─── Supplier configs ────────────────────────────────────────

    private function suppliers(): array
    {
        return [
            'toptex' => [
                'label' => 'Toptex',
                'command' => 'import:toptex',
                'suppliers' => ['Kariban', 'Front Row', 'Kariban Premium', 'Kimood', 'Proact', 'Toptex'],
                'auth_type' => 'JWT',
                'has_dump' => true,
                'has_prices' => false,
                'stock_flag' => '--stock-only',
            ],
            'solo' => [
                'label' => "Solo (SOL'S)",
                'command' => 'import:solo',
                'suppliers' => ["Sol's", 'Solo', 'NEOBLU', 'ATF', "Sol's (ATF)"],
                'auth_type' => 'OAuth2',
                'has_dump' => true,
                'has_prices' => true,
                'stock_flag' => '--stock-only',
            ],
            'roly' => [
                'label' => 'Roly',
                'command' => 'import:roly',
                'suppliers' => ['Roly'],
                'auth_type' => 'Basic',
                'has_dump' => false,
                'has_prices' => false,
                'stock_flag' => '--update-stock-only',
            ],
            'valento' => [
                'label' => 'Valento',
                'command' => 'import:valento',
                'suppliers' => ['Valento'],
                'auth_type' => 'Basic',
                'has_dump' => true,
                'has_prices' => true,
                'stock_flag' => '--stock-only',
            ],
            'imbretex' => [
                'label' => 'Imbretex',
                'command' => 'import:imbretex',
                'suppliers' => ['Imbretex', 'Pen-Duick', 'Reference Textile'],
                'auth_type' => 'Token',
                'has_dump' => true,
                'has_prices' => false,
                'stock_flag' => '--stock-only',
            ],
        ];
    }

    // ─── Public getters for the view ─────────────────────────────

    public function getSupplierTabs(): array
    {
        return $this->suppliers();
    }

    public function getProductCount(string $key): int
    {
        $config = $this->suppliers()[$key] ?? null;
        if (! $config) return 0;

        return Product::whereIn('supplier', $config['suppliers'])->count();
    }

    public function getColorCount(string $key): int
    {
        $config = $this->suppliers()[$key] ?? null;
        if (! $config) return 0;

        return Product::whereIn('supplier', $config['suppliers'])
            ->withCount('colors')
            ->get()
            ->sum('colors_count');
    }

    public function getBrands(string $key): string
    {
        $config = $this->suppliers()[$key] ?? null;
        if (! $config) return '';

        $counts = Product::whereIn('supplier', $config['suppliers'])
            ->selectRaw('supplier, count(*) as cnt')
            ->groupBy('supplier')
            ->pluck('cnt', 'supplier')
            ->toArray();

        if (empty($counts)) return 'Aucun produit';

        return collect($counts)
            ->map(fn ($cnt, $brand) => "{$brand} ({$cnt})")
            ->join(', ');
    }

    public function getAuthInfo(string $key): string
    {
        return match ($key) {
            'toptex' => $this->maskEmail(config('services.toptex.username', '')),
            'solo' => 'OAuth2 / ' . substr(config('services.solo.client_id', ''), 0, 12) . '...',
            'roly' => $this->maskEmail(config('services.roly.username', '')),
            'valento' => config('services.valento.username', 'Non configuré') . ' / ****',
            'imbretex' => $this->maskToken(config('services.imbretex.token', '')),
            default => 'N/A',
        };
    }

    public function getApiUrl(string $key): string
    {
        return match ($key) {
            'toptex' => config('services.toptex.base_url', 'Non configuré') . '/v3',
            'solo' => config('services.solo.base_url', 'Non configuré'),
            'roly' => config('services.roly.base_url', 'Non configuré'),
            'valento' => config('services.valento.base_url', 'Non configuré'),
            'imbretex' => config('services.imbretex.base_url', 'Non configuré'),
            default => 'N/A',
        };
    }

    // ─── Actions ─────────────────────────────────────────────────

    public function switchTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->output = '';
    }

    public function testConnection(): void
    {
        $key = $this->activeTab;

        try {
            $success = match ($key) {
                'toptex' => $this->testToptex(),
                'solo' => $this->testSolo(),
                'roly' => $this->testRoly(),
                'valento' => $this->testValento(),
                'imbretex' => $this->testImbretex(),
                default => false,
            };

            if ($success) {
                Notification::make()->title('Connexion réussie')->success()->send();
            } else {
                Notification::make()->title('Échec de connexion')->danger()->send();
            }
        } catch (\Throwable $e) {
            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
        }
    }

    public function runDumpStructure(): void
    {
        $this->runCommand(['--dump-structure' => true], 'Structure récupérée');
    }

    public function runDryRun(): void
    {
        $options = ['--dry-run' => true];
        if ($this->activeTab === 'solo') {
            $options['--limit'] = 50;
        }
        $this->runCommand($options, 'Dry run terminé');
    }

    public function runStockUpdate(): void
    {
        $config = $this->suppliers()[$this->activeTab] ?? null;
        if (! $config) return;

        $this->runCommand([$config['stock_flag'] => true], 'MAJ stocks terminée');
    }

    public function runPriceUpdate(): void
    {
        $this->runCommand(['--prices-only' => true], 'MAJ prix terminée');
    }

    public function runFullImport(): void
    {
        $this->runCommand([], 'Import complet terminé');
    }

    // ─── Internal ────────────────────────────────────────────────

    private function runCommand(array $options, string $successMessage): void
    {
        $config = $this->suppliers()[$this->activeTab] ?? null;
        if (! $config) return;

        $this->isRunning = true;
        $this->output = '';

        try {
            $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput;
            Artisan::call($config['command'], $options, $outputBuffer);
            $this->output = $outputBuffer->fetch();

            Notification::make()->title($successMessage)->success()->send();
        } catch (\Throwable $e) {
            $this->output = "Erreur: {$e->getMessage()}";
            Notification::make()->title('Erreur')->body($e->getMessage())->danger()->send();
        }

        $this->isRunning = false;
    }

    private function testToptex(): bool
    {
        $api = new \App\Services\ToptexApiService;
        $api->testConnection();
        return true;
    }

    private function testSolo(): bool
    {
        $result = app(\App\Services\SoloApiService::class)->testConnection();
        return $result['success'] ?? false;
    }

    private function testRoly(): bool
    {
        $api = new \App\Services\RolyApiService;
        $api->login();
        return true;
    }

    private function testValento(): bool
    {
        $api = new \App\Services\ValentoApiService;
        return $api->testConnection();
    }

    private function testImbretex(): bool
    {
        $api = new \App\Services\ImbretexApiService;
        return $api->testConnection();
    }

    private function maskEmail(string $value): string
    {
        if (! $value) return 'Non configuré';
        $parts = explode('@', $value);
        if (count($parts) === 2) {
            return substr($parts[0], 0, 3) . '***@' . $parts[1];
        }
        return substr($value, 0, 3) . '***';
    }

    private function maskToken(string $value): string
    {
        if (! $value) return 'Non configuré';
        return substr($value, 0, 6) . '***' . substr($value, -4);
    }
}
