<?php

namespace App\Console\Commands;

use App\Models\BrandColor;
use App\Models\BrandSize;
use App\Models\Product;
use App\Models\ProductSize;
use Illuminate\Console\Command;

class SeedBrandCatalogs extends Command
{
    protected $signature = 'brands:seed-catalogs';

    protected $description = 'Populate brand_colors and brand_sizes from existing product data';

    public function handle(): int
    {
        $this->info('Extraction des couleurs et tailles par marque...');

        $brands = Product::whereNotNull('supplier')
            ->distinct()
            ->pluck('supplier');

        $colorsCount = 0;
        $sizesCount = 0;

        foreach ($brands as $brand) {
            $this->info("Marque: {$brand}");

            // Colors
            $colors = Product::where('supplier', $brand)
                ->with('colors')
                ->get()
                ->flatMap(fn ($p) => $p->colors)
                ->unique('name');

            foreach ($colors as $color) {
                BrandColor::updateOrCreate(
                    ['brand' => $brand, 'name' => $color->name],
                    ['hex_code' => $color->hex_code]
                );
                $colorsCount++;
            }

            // Sizes
            $sizes = Product::where('supplier', $brand)
                ->with('colors.sizes')
                ->get()
                ->flatMap(fn ($p) => $p->colors)
                ->flatMap(fn ($c) => $c->sizes)
                ->pluck('size')
                ->unique();

            foreach ($sizes as $size) {
                BrandSize::updateOrCreate(
                    ['brand' => $brand, 'size' => $size]
                );
                $sizesCount++;
            }

            $this->line("  → {$colors->count()} couleurs, {$sizes->count()} tailles");
        }

        $this->info("Terminé: {$colorsCount} couleurs, {$sizesCount} tailles indexées.");

        return self::SUCCESS;
    }
}
