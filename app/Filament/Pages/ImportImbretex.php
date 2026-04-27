<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\ImbretexApiService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class ImportImbretex extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Import Imbretex';

    protected static ?string $title = 'Import Imbretex';

    protected static ?int $navigationSort = 11;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.import-imbretex';

    public string $output = '';

    public bool $isRunning = false;

    public function getProductCount(): int
    {
        return Product::whereIn('supplier', ['Imbretex', 'Pen-Duick', 'Reference Textile'])->count();
    }

    public function getColorCount(): int
    {
        return Product::whereIn('supplier', ['Imbretex', 'Pen-Duick', 'Reference Textile'])
            ->withCount('colors')
            ->get()
            ->sum('colors_count');
    }

    public function getBrands(): string
    {
        $counts = Product::whereIn('supplier', ['Imbretex', 'Pen-Duick', 'Reference Textile'])
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
        return config('services.imbretex.base_url', 'Non configuré');
    }

    public function getTokenStatus(): string
    {
        $token = config('services.imbretex.token', '');
        if (! $token) {
            return 'Non configuré';
        }

        return substr($token, 0, 6) . '***' . substr($token, -4);
    }

    public function testConnection(): void
    {
        try {
            $api = new ImbretexApiService;
            if ($api->testConnection()) {
                Notification::make()
                    ->title('Connexion réussie')
                    ->body('Authentification à l\'API Imbretex OK.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Échec de connexion')
                    ->body('L\'API retourne une erreur d\'authentification. Vérifiez le token.')
                    ->danger()
                    ->send();
            }
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
            Artisan::call('import:imbretex', [], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
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
            Artisan::call('import:imbretex', ['--stock-only' => true], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
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
            Artisan::call('import:imbretex', ['--dry-run' => true], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
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
            Artisan::call('import:imbretex', ['--dump-structure' => true], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
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
