<?php

namespace Database\Seeders;

use App\Models\GroupShop;
use App\Models\GroupShopProduct;
use App\Models\Product;
use App\Models\MarkingTechnique;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class GroupShopSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first();
        $broderie = MarkingTechnique::where('slug', 'broderie')->first();
        $serigraphie = MarkingTechnique::where('slug', 'serigraphie')->first();

        // Demo group shop — open, with password
        $shop = GroupShop::create([
            'name' => 'Entreprise Dupont - Tenues equipe',
            'slug' => 'entreprise-dupont',
            'password' => Hash::make('demo2026'),
            'description' => 'Boutique reservee aux collaborateurs de l\'entreprise Dupont pour la commande de tenues d\'equipe personnalisees.',
            'pricing_mode' => 'fixed',
            'payment_mode' => 'individual',
            'status' => 'open',
            'opens_at' => now()->subDays(5),
            'closes_at' => now()->addDays(25),
            'created_by' => $admin?->id,
        ]);

        // Add some products
        $products = Product::where('is_active', true)->limit(4)->get();

        foreach ($products as $i => $product) {
            $technique = $i < 2 ? $broderie : $serigraphie;
            $colorIds = $product->colors()->limit(4)->pluck('id')->toArray();
            $sizeIds = $product->sizes()
                ->whereIn('size', ['S', 'M', 'L', 'XL'])
                ->pluck('product_sizes.id')
                ->toArray();

            GroupShopProduct::create([
                'group_shop_id' => $shop->id,
                'product_id' => $product->id,
                'allowed_colors' => $colorIds ?: null,
                'allowed_sizes' => $sizeIds ?: null,
                'marking_technique_id' => $technique?->id,
                'marking_zone' => 'poitrine_gauche',
                'fixed_price' => round(rand(1500, 3500) / 100, 2),
                'sort_order' => $i,
            ]);
        }

        $this->command->info("Boutique groupee creee : {$shop->name} (slug: {$shop->slug}, mdp: demo2026)");
    }
}
