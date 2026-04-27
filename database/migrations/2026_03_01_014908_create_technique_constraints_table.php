<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('technique_constraints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marking_technique_id')->constrained()->cascadeOnDelete();
            $table->integer('min_quantity')->default(1);
            $table->integer('max_colors')->nullable();
            $table->integer('max_visual_width')->nullable();
            $table->integer('max_visual_height')->nullable();
            $table->integer('production_days_min')->nullable();
            $table->integer('production_days_max')->nullable();
            $table->json('compatible_materials')->nullable();
            $table->json('incompatible_materials')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique('marking_technique_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('technique_constraints');
    }
};
