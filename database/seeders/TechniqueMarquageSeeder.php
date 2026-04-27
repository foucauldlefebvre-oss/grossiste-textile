<?php

namespace Database\Seeders;

use App\Models\TechniqueMarquage;
use Illuminate\Database\Seeder;

class TechniqueMarquageSeeder extends Seeder
{
    public function run(): void
    {
        $techniques = [
            ['nom' => 'Broderie', 'description_courte' => 'Dès 20 pièces', 'ordre' => 1],
            ['nom' => 'Sérigraphie', 'description_courte' => 'Dès 20 pièces', 'ordre' => 2],
            ['nom' => 'Impression directe DTG', 'description_courte' => 'Dès 10 pièces', 'ordre' => 3],
            ['nom' => 'Flex & velour', 'description_courte' => 'Dès 10 pièces', 'ordre' => 4],
            ['nom' => 'Sublimation', 'description_courte' => 'Dès 10 pièces', 'ordre' => 5],
            ['nom' => 'Transfert numérique', 'description_courte' => 'Dès 50 pièces', 'ordre' => 6],
            ['nom' => 'Tissage', 'description_courte' => 'Dès 100 pièces', 'ordre' => 7],
            ['nom' => 'Flocage', 'description_courte' => 'Dès 10 pièces', 'ordre' => 8],
        ];

        foreach ($techniques as $t) {
            TechniqueMarquage::updateOrCreate(
                ['nom' => $t['nom']],
                $t
            );
        }
    }
}
