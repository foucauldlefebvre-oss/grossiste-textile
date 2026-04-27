<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Categories tree
echo "=== CATEGORIES ===\n";
$cats = DB::table('categories')->select('id', 'name', 'slug', 'parent_id')->orderBy('parent_id')->orderBy('name')->get();
foreach ($cats as $c) {
    $prefix = $c->parent_id ? "  └─ " : "";
    $count = DB::table('products')->where('category_id', $c->id)->count();
    echo "$prefix{$c->name} (id={$c->id}, parent={$c->parent_id}) — {$count} produits\n";
}

// Check material/description patterns to detect cotton/polyester
echo "\n=== MATERIAL SAMPLES ===\n";
$materials = DB::table('products')->whereNotNull('material')->where('material', '!=', '')
    ->selectRaw('material, count(*) as cnt')->groupBy('material')->orderByDesc('cnt')->limit(30)->get();
foreach ($materials as $m) {
    echo "  [{$m->cnt}] {$m->material}\n";
}

// Products with technique rules - which categories?
echo "\n=== Categories with existing technique rules ===\n";
$ruledCats = DB::table('product_technique_rules as ptr')
    ->join('products as p', 'p.id', '=', 'ptr.product_id')
    ->join('categories as c', 'c.id', '=', 'p.category_id')
    ->selectRaw('c.name, c.id, count(distinct p.id) as product_count')
    ->groupBy('c.id', 'c.name')
    ->orderByDesc('product_count')
    ->get();
foreach ($ruledCats as $rc) {
    echo "  {$rc->name} (id={$rc->id}): {$rc->product_count} products\n";
}
