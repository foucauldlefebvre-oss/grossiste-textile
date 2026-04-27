<?php

namespace App\Console\Commands;

use App\Models\PricingRule;
use App\Models\Product;
use Illuminate\Console\Command;

class ApplyPricingRules extends Command
{
    protected $signature = 'products:apply-pricing
        {--dry-run : Show what would be done without modifying anything}
        {--quantity=100 : Default quantity for coefficient lookup}';

    protected $description = 'Apply pricing coefficients to products: base_price = supplier_price × coefficient';

    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $defaultQty = (int) $this->option('quantity');

        if ($dryRun) {
            $this->info('=== DRY RUN MODE ===');
        }

        $rules = PricingRule::orderBy('sort_order')->get();
        if ($rules->isEmpty()) {
            $this->error('No pricing rules found. Run: php artisan db:seed --class=PricingRuleSeeder');
            return self::FAILURE;
        }

        // Show the pricing grid
        $this->info("Pricing grid (default quantity: {$defaultQty}):");
        $priceTiers = $rules->unique('label')->pluck('label');
        foreach ($priceTiers as $label) {
            $coeff = $rules->where('label', $label)
                ->where('min_quantity', '<=', $defaultQty)
                ->filter(fn ($r) => $r->max_quantity === null || $r->max_quantity >= $defaultQty)
                ->sortByDesc('min_quantity')
                ->first();
            $c = $coeff ? $coeff->coefficient : '?';
            $this->line("  {$label}: x{$c}");
        }
        $this->info('');

        $products = Product::whereNotNull('base_price')->where('base_price', '>', 0)->get();
        $this->info("Processing {$products->count()} products...");

        $stats = [
            'supplier_price_copied' => 0,
            'prices_updated' => 0,
            'no_rule_found' => 0,
        ];

        $tierStats = [];

        foreach ($products as $product) {
            // Step 1: Copy base_price to supplier_price if supplier_price is empty
            $supplierPrice = (float) $product->supplier_price;
            if (! $supplierPrice) {
                $supplierPrice = (float) $product->base_price;
                if (! $dryRun) {
                    $product->supplier_price = $supplierPrice;
                }
                $stats['supplier_price_copied']++;
            }

            // Step 2: Find coefficient
            $coefficient = PricingRule::getCoefficient($supplierPrice, $defaultQty);

            if ($coefficient <= 0) {
                $stats['no_rule_found']++;
                continue;
            }

            // Step 3: Calculate selling price
            $sellingPrice = round($supplierPrice * $coefficient, 2);

            // Track by tier
            $tierLabel = $this->getTierLabel($rules, $supplierPrice);
            if (! isset($tierStats[$tierLabel])) {
                $tierStats[$tierLabel] = ['count' => 0, 'coeff' => $coefficient];
            }
            $tierStats[$tierLabel]['count']++;

            if (! $dryRun) {
                $product->base_price = $sellingPrice;
                $product->save();
            }

            $stats['prices_updated']++;
        }

        $this->info('');
        $this->info('=== Summary ===');
        foreach ($stats as $key => $val) {
            $this->line('  ' . str_replace('_', ' ', $key) . ': ' . $val);
        }

        $this->info('');
        $this->info('By price tier:');
        foreach ($tierStats as $label => $data) {
            $this->line("  {$label}: {$data['count']} products (x{$data['coeff']})");
        }

        if ($dryRun) {
            $this->info('');
            $this->warn('Dry run complete. No changes were made.');
        }

        return self::SUCCESS;
    }

    private function getTierLabel($rules, float $price): string
    {
        foreach ($rules->unique('label') as $rule) {
            if ($price >= (float) $rule->min_price && ($rule->max_price === null || $price <= (float) $rule->max_price)) {
                return $rule->label;
            }
        }

        return 'unknown';
    }
}
