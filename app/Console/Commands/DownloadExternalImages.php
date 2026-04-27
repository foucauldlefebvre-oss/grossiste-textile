<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductColor;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DownloadExternalImages extends Command
{
    protected $signature = 'images:download
        {--products : Telecharger uniquement les images produit principales}
        {--colors : Telecharger uniquement les images couleur}
        {--gallery : Telecharger uniquement les images gallery}
        {--supplier= : Filtrer par fournisseur}
        {--limit=0 : Limiter le nombre d\'images}
        {--dry-run : Compter sans telecharger}';

    protected $description = 'Telecharge les images externes et les stocke en local';

    private int $downloaded = 0;
    private int $failed = 0;
    private int $skipped = 0;

    public function handle(): int
    {
        $hasFilter = $this->option('products') || $this->option('colors') || $this->option('gallery');
        $doProducts = $this->option('products') || ! $hasFilter;
        $doColors = $this->option('colors') || ! $hasFilter;
        $doGallery = $this->option('gallery') || ! $hasFilter;
        $supplier = $this->option('supplier');
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        if ($doProducts) {
            $this->downloadProductImages($supplier, $limit, $dryRun);
        }

        if ($doColors) {
            $this->downloadColorImages($supplier, $limit, $dryRun);
        }

        if ($doGallery) {
            $this->downloadGalleryImages($supplier, $limit, $dryRun);
        }

        $this->newLine();
        $this->info('=== Resume ===');
        $this->table(['Metrique', 'Total'], [
            ['Telecharges', $this->downloaded],
            ['Echoues', $this->failed],
            ['Ignores (deja local)', $this->skipped],
        ]);

        return self::SUCCESS;
    }

    private function downloadProductImages(?string $supplier, int $limit, bool $dryRun): void
    {
        $query = Product::where('main_image', 'like', 'http%');
        if ($supplier) {
            $query->where('supplier', $supplier);
        }
        if ($limit > 0) {
            $query->limit($limit);
        }

        $products = $query->get(['id', 'name', 'supplier', 'main_image']);
        $this->info("Images produit a telecharger : {$products->count()}");

        if ($dryRun) {
            return;
        }

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $bar->advance();

            $localPath = $this->downloadImage(
                $product->main_image,
                'products/' . Str::slug($product->supplier),
                Str::slug($product->name)
            );

            if ($localPath) {
                $product->update(['main_image' => $localPath]);

                // Aussi mettre a jour la gallery si elle contient des URLs externes
                if (is_array($product->gallery)) {
                    $newGallery = [];
                    foreach ($product->gallery as $i => $url) {
                        if (str_starts_with($url, 'http')) {
                            $dlPath = $this->downloadImage($url, 'products/' . Str::slug($product->supplier), Str::slug($product->name) . '-' . ($i + 1));
                            $newGallery[] = $dlPath ?: $url;
                        } else {
                            $newGallery[] = $url;
                        }
                    }
                    $product->update(['gallery' => $newGallery]);
                }
            }
        }

        $bar->finish();
        $this->newLine();
    }

    private function downloadColorImages(?string $supplier, int $limit, bool $dryRun): void
    {
        $query = ProductColor::where('image', 'like', 'http%');
        if ($supplier) {
            $query->whereHas('product', fn ($q) => $q->where('supplier', $supplier));
        }
        if ($limit > 0) {
            $query->limit($limit);
        }

        $colors = $query->with('product:id,name,supplier')->get(['id', 'product_id', 'name', 'image']);
        $this->info("Images couleur a telecharger : {$colors->count()}");

        if ($dryRun) {
            return;
        }

        $bar = $this->output->createProgressBar($colors->count());
        $bar->start();

        foreach ($colors as $color) {
            $bar->advance();

            $supplierSlug = Str::slug($color->product?->supplier ?? 'unknown');
            $productSlug = Str::slug($color->product?->name ?? 'product');
            $colorSlug = Str::slug($color->name);

            $localPath = $this->downloadImage(
                $color->image,
                "products/{$supplierSlug}/{$productSlug}",
                $colorSlug
            );

            if ($localPath) {
                $color->update(['image' => $localPath]);
            }
        }

        $bar->finish();
        $this->newLine();
    }

    private function downloadGalleryImages(?string $supplier, int $limit, bool $dryRun): void
    {
        $query = Product::whereNotNull('gallery')->where('gallery', 'like', '%http%');
        if ($supplier) {
            $query->where('supplier', $supplier);
        }
        if ($limit > 0) {
            $query->limit($limit);
        }

        $products = $query->get(['id', 'name', 'supplier', 'gallery']);
        $totalUrls = 0;
        foreach ($products as $p) {
            $gallery = is_array($p->gallery) ? $p->gallery : json_decode($p->gallery, true);
            if (is_array($gallery)) {
                $totalUrls += count(array_filter($gallery, fn ($url) => str_starts_with($url, 'http')));
            }
        }
        $this->info("Galleries a traiter : {$products->count()} produits ({$totalUrls} URLs externes)");

        if ($dryRun) {
            return;
        }

        $bar = $this->output->createProgressBar($products->count());
        $bar->start();

        foreach ($products as $product) {
            $bar->advance();
            $gallery = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);
            if (! is_array($gallery)) {
                continue;
            }

            $supplierSlug = Str::slug($product->supplier);
            $productSlug = Str::slug($product->name);
            $changed = false;

            foreach ($gallery as $i => &$url) {
                if (str_starts_with($url, 'http')) {
                    $dlPath = $this->downloadImage($url, "products/{$supplierSlug}", "{$productSlug}-" . ($i + 1));
                    if ($dlPath) {
                        $url = $dlPath;
                        $changed = true;
                    }
                }
            }
            unset($url);

            if ($changed) {
                $product->update(['gallery' => $gallery]);
            }
        }

        $bar->finish();
        $this->newLine();
    }

    private function downloadImage(string $url, string $directory, string $filename): ?string
    {
        try {
            $response = Http::timeout(15)
                ->withOptions(['verify' => false])
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/131.0.0.0 Safari/537.36',
                    'Accept' => 'image/webp,image/apng,image/*,*/*;q=0.8',
                ])
                ->get($url);

            if (! $response->successful() || $response->body() === '' || strlen($response->body()) < 100) {
                $this->failed++;

                return null;
            }

            // Detecter l'extension
            $contentType = $response->header('Content-Type');
            $ext = match (true) {
                str_contains($contentType ?? '', 'png') => 'png',
                str_contains($contentType ?? '', 'webp') => 'webp',
                str_contains($contentType ?? '', 'gif') => 'gif',
                default => 'jpg',
            };

            // Fallback extension depuis l'URL
            $urlExt = strtolower(pathinfo(parse_url($url, PHP_URL_PATH), PATHINFO_EXTENSION));
            if (in_array($urlExt, ['jpg', 'jpeg', 'png', 'webp', 'gif'])) {
                $ext = $urlExt === 'jpeg' ? 'jpg' : $urlExt;
            }

            $path = "{$directory}/{$filename}.{$ext}";

            // Ne pas re-telecharger si existe deja
            if (Storage::disk('public')->exists($path)) {
                $this->skipped++;

                return $path;
            }

            Storage::disk('public')->put($path, $response->body());
            $this->downloaded++;

            return $path;
        } catch (\Throwable $e) {
            $this->failed++;

            return null;
        }
    }
}
