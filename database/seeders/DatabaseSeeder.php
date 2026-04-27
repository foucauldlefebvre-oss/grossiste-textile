<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@marquage-textile.fr',
        ]);

        // TODO 2b: MarkingTechniqueSeeder supprimé (techniques dégagées)
        $this->call([
            CategoryStructureSeeder::class,
            CategoryProductSeeder::class,
            GroupShopSeeder::class,
            ShippingRateSeeder::class,
        ]);
    }
}
