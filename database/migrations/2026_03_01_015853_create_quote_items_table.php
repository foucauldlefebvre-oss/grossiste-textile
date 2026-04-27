<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quote_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quote_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_size_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marking_technique_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price_ht', 10, 2)->default(0);
            $table->decimal('marking_price_ht', 10, 2)->default(0);
            $table->decimal('line_total_ht', 10, 2)->default(0);
            $table->string('visual_file')->nullable();
            $table->string('marking_zone')->nullable();
            $table->integer('visual_colors')->nullable();
            $table->json('options')->nullable();
            $table->timestamps();

            $table->index('quote_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quote_items');
    }
};
