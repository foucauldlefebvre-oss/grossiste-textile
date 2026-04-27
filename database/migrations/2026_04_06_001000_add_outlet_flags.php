<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->boolean('is_outlet')->default(false)->after('is_active');
        });

        Schema::table('product_colors', function (Blueprint $table) {
            $table->boolean('is_outlet')->default(false)->after('is_active');
            $table->decimal('outlet_supplier_price', 10, 2)->nullable()->after('is_outlet');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('is_outlet');
        });
        Schema::table('product_colors', function (Blueprint $table) {
            $table->dropColumn(['is_outlet', 'outlet_supplier_price']);
        });
    }
};
