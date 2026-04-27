<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('serigraphie_pricings', function (Blueprint $table) {
            $table->id();
            $table->enum('textile_type', ['light', 'dark']);
            $table->unsignedTinyInteger('num_colors');
            $table->unsignedInteger('quantity_min');
            $table->unsignedInteger('quantity_max')->nullable();
            $table->decimal('unit_price', 8, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['textile_type', 'num_colors', 'quantity_min'], 'seri_type_colors_qty');
        });

        Schema::table('marking_techniques', function (Blueprint $table) {
            $table->unsignedTinyInteger('quality_rating')->nullable()->after('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('serigraphie_pricings');

        Schema::table('marking_techniques', function (Blueprint $table) {
            $table->dropColumn('quality_rating');
        });
    }
};
