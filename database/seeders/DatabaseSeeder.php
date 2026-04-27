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

        $this->call([
            MarkingTechniqueSeeder::class,
            CategoryStructureSeeder::class,
            CategoryProductSeeder::class,
            GroupShopSeeder::class,
            ShippingRateSeeder::class,
        ]);
    }
}
