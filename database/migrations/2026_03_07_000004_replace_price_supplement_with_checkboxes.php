<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_technique_rules', function (Blueprint $table) {
            $table->dropColumn('price_supplement');
            $table->boolean('supplement_10')->default(false)->after('marking_zones');
            $table->boolean('supplement_20')->default(false)->after('supplement_10');
        });
    }

    public function down(): void
    {
        Schema::table('product_technique_rules', function (Blueprint $table) {
            $table->dropColumn(['supplement_10', 'supplement_20']);
            $table->decimal('price_supplement', 8, 2)->default(0)->after('marking_zones');
        });
    }
};
