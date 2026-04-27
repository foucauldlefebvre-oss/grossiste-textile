<?php

namespace App\Filament\Pages;

use App\Models\Product;
use App\Services\RolyApiService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\Artisan;

class ImportRoly extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-arrow-down-tray';

    protected static ?string $navigationGroup = 'Catalogue';

    protected static ?string $navigationLabel = 'Import Roly';

    protected static ?string $title = 'Import Roly';

    protected static ?int $navigationSort = 10;

    protected static bool $shouldRegisterNavigation = false;

    protected static string $view = 'filament.pages.import-roly';

    public string $output = '';
    public bool $isRunning = false;

    public function getProductCount(): int
    {
        return Product::where('supplier', 'Roly')->count();
    }

    public function getColorCount(): int
    {
        return Product::where('supplier', 'Roly')
            ->withCount('colors')
            ->get()
            ->sum('colors_count');
    }

    public function getUsername(): string
    {
        $username = config('services.roly.username', '');
        if (! $username) {
            return 'Non configuré';
        }

        // Mask: show first 3 chars + domain if email
        $parts = explode('@', $username);
        if (count($parts) === 2) {
            return substr($parts[0], 0, 3) . '***@' . $parts[1];
        }

        return substr($username, 0, 3) . '***';
    }

    public function getApiUrl(): string
    {
        return config('services.roly.base_url', 'https://clientsws.gorfactory.es:2096');
    }

    public function testConnection(): void
    {
        try {
            $api = new RolyApiService();
            $api->login();

            Notification::make()
                ->title('Connexion réussie')
                ->body('Authentification à l\'API Roly OK.')
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
            Artisan::call('import:roly', [], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput());
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
            Artisan::call('import:roly', ['--update-stock-only' => true], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput());
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
            Artisan::call('import:roly', ['--dry-run' => true], $outputBuffer = new \Symfony\Component\Console\Output\BufferedOutput());
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
}
