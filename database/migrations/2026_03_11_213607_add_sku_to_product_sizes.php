<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->string('sku')->nullable()->after('size');
            $table->string('ean')->nullable()->after('sku');
            $table->index('sku', 'product_sizes_sku_index');
        });
    }

    public function down(): void
    {
        Schema::table('product_sizes', function (Blueprint $table) {
            $table->dropIndex('product_sizes_sku_index');
            $table->dropColumn(['sku', 'ean']);
        });
    }
};
