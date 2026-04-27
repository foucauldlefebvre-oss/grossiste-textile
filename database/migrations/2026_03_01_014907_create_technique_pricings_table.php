<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technique_pricings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marking_technique_id')->constrained()->cascadeOnDelete();
            $table->integer('quantity_min');
            $table->integer('quantity_max')->nullable();
            $table->decimal('unit_price', 10, 2);
            $table->decimal('setup_cost', 10, 2)->default(0);
            $table->integer('num_colors')->default(1);
            $table->timestamps();

            $table->index('marking_technique_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technique_pricings');
    }
};
