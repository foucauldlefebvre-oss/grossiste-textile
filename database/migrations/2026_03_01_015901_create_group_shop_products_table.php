<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_shop_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->json('allowed_colors')->nullable();
            $table->json('allowed_sizes')->nullable();
            $table->foreignId('marking_technique_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visual_file')->nullable();
            $table->string('marking_zone')->nullable();
            $table->decimal('fixed_price', 10, 2)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['group_shop_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_shop_products');
    }
};
