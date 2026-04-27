<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Composite index for frequent filtered queries (supplier + is_active)
        Schema::table('products', function (Blueprint $table) {
            $table->index(['supplier', 'is_active'], 'products_supplier_active_index');
            $table->index(['category_id', 'is_active'], 'products_category_active_index');
            $table->index(['is_active', 'created_at'], 'products_active_created_index');
        });

        // Slug redirects table for 301 redirects (old slugs → new products)
        Schema::create('slug_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('old_slug')->unique();
            $table->string('new_slug');
            $table->string('type')->default('product'); // product or category
            $table->timestamps();
        });

        // Index on product_colors for eager loading
        Schema::table('product_colors', function (Blueprint $table) {
            $table->index(['product_id', 'is_active'], 'product_colors_product_active_index');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex('products_supplier_active_index');
            $table->dropIndex('products_category_active_index');
            $table->dropIndex('products_active_created_index');
        });

        Schema::dropIfExists('slug_redirects');

        Schema::table('product_colors', function (Blueprint $table) {
            $table->dropIndex('product_colors_product_active_index');
        });
    }
};
