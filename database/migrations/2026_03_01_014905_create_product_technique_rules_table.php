<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_technique_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('marking_technique_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_compatible')->default(true);
            $table->string('incompatibility_reason')->nullable();
            $table->json('marking_zones')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'marking_technique_id'], 'product_technique_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_technique_rules');
    }
};
