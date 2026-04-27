<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\SoloApiService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class ImportSolo extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Import Solo (SOL\'S)';

    protected static ?string $title = 'Import Solo Paris (SOL\'S)';

    protected static ?int $navigationSort = 13;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.import-solo';

    public string $output = '';

    public bool $isRunning = false;

    public function getProductCount(): int
    {
        return Product::whereIn('supplier', ["Sol's", 'Solo', 'NEOBLU', 'ATF', "Sol's (ATF)"])->count();
    }

    public function getApiUrl(): string
    {
        return config('services.solo.base_url', 'Non configuré');
    }

    public function getAuthStatus(): string
    {
        $clientId = config('services.solo.client_id', '');
        if (! $clientId) {
            return 'Non configuré';
        }

        return 'OAuth2 / ' . substr($clientId, 0, 12) . '...';
    }

    public function getTokenUrl(): string
    {
        return config('services.solo.token_url', 'Non configuré');
    }

    public function testConnection(): void
    {
        try {
            $api = app(SoloApiService::class);
            $result = $api->testConnection();

            if ($result['success']) {
                Notification::make()
                    ->title('Connexion OAuth2 réussie')
                    ->body("Token: {$result['token_preview']}")
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Échec OAuth2')
                    ->body($result['message'])
                    ->danger()
                    ->send();
            }
        } catch (\Throwable $e) {
            Notification::make()
                ->title('Erreur')
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
        $this->runCommand(['--dry-run' => true, '--limit' => 50], 'Dry run terminé');
    }

    public function runStockUpdate(): void
    {
        $this->runCommand(['--stock-only' => true], 'Mise à jour stocks terminée');
    }

    public function runPriceUpdate(): void
    {
        $this->runCommand(['--prices-only' => true], 'Mise à jour prix terminée');
    }

    public function runFullImport(): void
    {
        $this->runCommand([], 'Import complet terminé');
    }

    private function runCommand(array $options, string $successMessage): void
    {
        $this->isRunning = true;
        $this->output = '';

        try {
            Artisan::call('import:solo', $options, $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput);
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
