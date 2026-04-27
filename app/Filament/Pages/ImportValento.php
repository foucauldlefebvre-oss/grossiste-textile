<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\ValentoApiService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class ImportValento extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Import Valento';

    protected static ?string $title = 'Import Valento';

    protected static ?int $navigationSort = 12;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.import-valento';

    public string $output = '';

    public bool $isRunning = false;

    public function getProductCount(): int
    {
        return Product::where('supplier', 'Valento')->count();
    }

    public function getApiUrl(): string
    {
        return config('services.valento.base_url', 'Non configuré');
    }

    public function getAuthStatus(): string
    {
        $user = config('services.valento.username', '');
        if (! $user) {
            return 'Non configuré';
        }

        return $user . ' / ****';
    }

    public function testConnection(): void
    {
        try {
            $api = new ValentoApiService;
            if ($api->testConnection()) {
                Notification::make()
                    ->title('Connexion réussie')
                    ->body('Authentification à l\'API Valento OK.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Échec de connexion')
                    ->body('L\'API retourne une erreur. Vérifiez les identifiants.')
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

    public function runDumpStructure(): void
    {
        $this->runCommand(['--dump-structure' => true], 'Structure récupérée');
    }

    public function runDryRun(): void
    {
        $this->runCommand(['--dry-run' => true], 'Dry run terminé');
    }

    public function runStockUpdate(): void
    {
        $this->runCommand(['--stock-only' => true], 'Mise à jour stocks terminée');
    }

    public function runPriceUpdate(): void
    {
        $this->runCommand(['--prices-only' => true], 'Mise à jour prix terminée');
    }

    public function runFullUpdate(): void
    {
        $this->runCommand([], 'Import stocks + prix terminé');
    }

    private function runCommand(array $options, string $successMessage): void
    {
        $this->isRunning = true;
        $this->output = '';

        try {
            Artisan::call('import:valento', $options, $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
            $this->output = $outputBuffer->fetch();

            Notification::make()
                ->title($successMessage)
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
}
