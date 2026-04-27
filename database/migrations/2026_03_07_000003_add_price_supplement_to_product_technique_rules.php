<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_technique_rules', function (Blueprint $table) {
            $table->decimal('price_supplement', 8, 2)->default(0)->after('marking_zones');
        });

        // Set quality_rating for all techniques in the correct order
        $ratings = [
            'broderie' => 5.0,
            'transfert' => 4.5,
            'transfert-offset' => 4.0,
            'serigraphie' => 3.5,
            'badge-pvc' => 3.0,
            'tissage' => 2.5,
            'impression-dtg' => 2.0,
            'flocage-flex' => 1.5,
            'dtf' => 1.0,
        ];

        foreach ($ratings as $slug => $rating) {
            DB::table('marking_techniques')
                ->where('slug', $slug)
                ->update(['quality_rating' => $rating]);
        }
    }

    public function down(): void
    {
        Schema::table('product_technique_rules', function (Blueprint $table) {
            $table->dropColumn('price_supplement');
        });
    }
};
