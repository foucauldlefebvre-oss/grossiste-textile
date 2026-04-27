<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Old products (reference NOT starting with typical Excel codes)
$oldProducts = App\Models\Product::where('supplier', 'Roly')
    ->whereNotIn('reference', function($q) {
        $q->select('reference')->from('products')
          ->where('supplier', 'Roly')
          ->where('created_at', '>', '2026-03-14');
    })
    ->where('created_at', '<', '2026-03-14')
    ->get(['id', 'name', 'reference', 'slug', 'seo_url', 'prestashop_id', 'category_id', 'base_price']);

echo "Old Roly products: " . $oldProducts->count() . "\n\n";

// Show sample with all relevant fields
echo "=== Sample old products ===\n";
foreach ($oldProducts->take(20) as $p) {
    echo "id:{$p->id} | ref:{$p->reference} | name:{$p->name} | slug:{$p->slug} | seo_url:{$p->seo_url} | ps_id:{$p->prestashop_id}\n";
}

echo "\n=== All old product names ===\n";
foreach ($oldProducts as $p) {
    echo "{$p->id}|{$p->name}|{$p->reference}\n";
}
