<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\ToptexApiService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class ImportToptex extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Import Toptex';

    protected static ?string $title = 'Import Toptex';

    protected static ?int $navigationSort = 12;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.import-toptex';

    public string $output = '';

    public bool $isRunning = false;

    /** Known Toptex-distributed brand names */
    private array $suppliers = [
        'Kariban', 'Front Row', 'Kariban Premium', 'Kimood', 'Proact', 'Toptex',
    ];

    public function getProductCount(): int
    {
        return Product::whereIn('supplier', $this->suppliers)->count();
    }

    public function getColorCount(): int
    {
        return Product::whereIn('supplier', $this->suppliers)
            ->withCount('colors')
            ->get()
            ->sum('colors_count');
    }

    public function getBrands(): string
    {
        $counts = Product::whereIn('supplier', $this->suppliers)
            ->selectRaw('supplier, count(*) as cnt')
            ->groupBy('supplier')
            ->pluck('cnt', 'supplier')
            ->toArray();

        if (empty($counts)) {
            return 'Aucun produit';
        }

        return collect($counts)
            ->map(fn ($cnt, $brand) => "{$brand} ({$cnt})")
            ->join(', ');
    }

    public function getApiUrl(): string
    {
        return config('services.toptex.base_url', 'Non configuré') . '/v3';
    }

    public function getKeyStatus(): string
    {
        $username = config('services.toptex.username', '');
        if (! $username) {
            return 'Non configuré';
        }

        $parts = explode('@', $username);
        if (count($parts) === 2) {
            return substr($parts[0], 0, 3) . '***@' . $parts[1];
        }

        return substr($username, 0, 3) . '***';
    }

    public function testConnection(): void
    {
        try {
            $api = new ToptexApiService;
            $api->testConnection();

            Notification::make()
                ->title('Connexion réussie')
                ->body('Authentification JWT Toptex OK.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Échec de connexion')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function runImport(): void
    {
        $this->isRunning = true;
        $this->output = '';

        try {
            Artisan::call('import:toptex', [], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
            $this->output = $outputBuffer->fetch();

            Notification::make()
                ->title('Import terminé')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->output = "Erreur: {$e->getMessage()}";

            Notification::make()
                ->title('Erreur lors de l\'import')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->isRunning = false;
    }

    public function runStockUpdate(): void
    {
        $this->isRunning = true;
        $this->output = '';

        try {
            Artisan::call('import:toptex', ['--stock-only' => true], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
            $this->output = $outputBuffer->fetch();

            Notification::make()
                ->title('Mise à jour stocks/prix terminée')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            $this->output = "Erreur: {$e->getMessage()}";

            Notification::make()
                ->title('Erreur lors de la mise à jour')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->isRunning = false;
    }

    public function runDryRun(): void
    {
        $this->isRunning = true;
        $this->output = '';

        try {
            Artisan::call('import:toptex', ['--dry-run' => true], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
            $this->output = $outputBuffer->fetch();

            Notification::make()
                ->title('Dry run terminé')
                ->info()
                ->send();
        } catch (\Throwable $e) {
            $this->output = "Erreur: {$e->getMessage()}";

            Notification::make()
                ->title('Erreur')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->isRunning = false;
    }

    public function runDumpStructure(): void
    {
        $this->isRunning = true;
        $this->output = '';

        try {
            Artisan::call('import:toptex', ['--dump-structure' => true], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
            $this->output = $outputBuffer->fetch();

            Notification::make()
                ->title('Structure récupérée')
                ->info()
                ->send();
        } catch (\Throwable $e) {
            $this->output = "Erreur: {$e->getMessage()}";

            Notification::make()
                ->title('Erreur')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }

        $this->isRunning = false;
    }
}
