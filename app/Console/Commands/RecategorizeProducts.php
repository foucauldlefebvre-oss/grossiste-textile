<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use PhpOffice\PhpSpreadsheet\IOFactory;

class RecategorizeProducts extends Command
{
    protected $signature = 'products:recategorize
        {--dry-run : Show what would be done without modifying anything}
        {--brand= : Only process a specific brand (e.g. Valento)}';

    protected $description = 'Apply recategorization from recategorisation-produits.xlsx: update categories, disable "à retirer" products';

    public function handle(): int
    {
        $file = base_path('recategorisation-produits.xlsx');
        if (! file_exists($file)) {
            $this->error("File not found: {$file}");
            return 1;
        }

        $dryRun = $this->option('dry-run');
        $brandFilter = $this->option('brand');

        if ($dryRun) {
            $this->warn('=== DRY RUN MODE ===');
        }

        $spreadsheet = IOFactory::load($file);
        $sheet = $spreadsheet->getSheetByName('Produits');
        if (! $sheet) {
            $this->error('Sheet "Produits" not found');
            return 1;
        }

        $rows = $sheet->toArray(null, true, true, true);
        $headers = $rows[1] ?? [];
        unset($rows[1]);

        $stats = [
            'total' => 0,
            'skipped' => 0,
            'disabled' => 0,
            'recategorized' => 0,
            'secondary_added' => 0,
            'already_ok' => 0,
            'not_found' => 0,
        ];

        foreach ($rows as $rowNum => $row) {
            $status = $row['A'] ?? null;
            $productId = $row['B'] ?? null;
            $brand = $row['C'] ?? null;
            $reference = $row['D'] ?? null;
            $name = $row['E'] ?? null;
            $catActuelle = $row['F'] ?? null;
            $catPrincipale = $row['G'] ?? null;
            $catSec1 = $row['H'] ?? null;
            $catSec2 = $row['I'] ?? null;

            if (! $reference || ! $brand) {
                continue;
            }

            if ($brandFilter && stripos($brand, $brandFilter) === false) {
                continue;
            }

            $stats['total']++;

            // Match by reference, NOT by ID (Excel IDs may not match DB IDs)
            $product = Product::where('reference', $reference)->first();
            if (! $product) {
                $this->warn("  Reference {$reference} ({$name}) not found in DB");
                $stats['not_found']++;
                continue;
            }

            $productId = $product->id;

            $catPrincipaleStr = trim((string) $catPrincipale);

            // Handle "à retirer"
            if (stripos($catPrincipaleStr, 'retirer') !== false) {
                if ($product->is_active) {
                    $this->line("  <fg=red>DISABLE</> ID:{$productId} | {$reference} | {$name}");
                    if (! $dryRun) {
                        $product->update(['is_active' => false]);
                    }
                    $stats['disabled']++;
                } else {
                    $stats['already_ok']++;
                }
                continue;
            }

            // Skip if no target category
            $targetCatId = is_numeric($catPrincipaleStr) ? (int) $catPrincipaleStr : null;
            if (! $targetCatId) {
                $stats['skipped']++;
                continue;
            }

            $changed = false;

            // Update main category if different
            if ($product->category_id !== $targetCatId) {
                $this->line("  <fg=yellow>RECAT</> ID:{$productId} | {$reference} | {$name} | cat {$product->category_id} → {$targetCatId}");
                if (! $dryRun) {
                    $product->update(['category_id' => $targetCatId]);
                }
                $changed = true;
                $stats['recategorized']++;
            }

            // Handle secondary categories
            $secondaryIds = [];
            if ($catSec1 && is_numeric(trim((string) $catSec1))) {
                $secondaryIds[] = (int) trim((string) $catSec1);
            }
            if ($catSec2 && is_numeric(trim((string) $catSec2))) {
                $secondaryIds[] = (int) trim((string) $catSec2);
            }

            if (! empty($secondaryIds)) {
                $currentSecondary = $product->secondaryCategories()->pluck('categories.id')->toArray();
                $toAdd = array_diff($secondaryIds, $currentSecondary);

                if (! empty($toAdd)) {
                    $this->line("  <fg=cyan>+SEC</> ID:{$productId} | {$reference} | add secondary: " . implode(', ', $toAdd));
                    if (! $dryRun) {
                        $product->secondaryCategories()->syncWithoutDetaching($secondaryIds);
                    }
                    $stats['secondary_added']++;
                    $changed = true;
                }
            }

            if (! $changed) {
                $stats['already_ok']++;
            }
        }

        $this->newLine();
        $this->info('=== RESULTS ===');
        $this->table(
            ['Metric', 'Count'],
            collect($stats)->map(fn ($v, $k) => [$k, $v])->values()->toArray()
        );

        if ($dryRun) {
            $this->warn('Dry run — no changes were made.');
        }

        return 0;
    }
}
