<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_colors', function (Blueprint $table) {
            $table->decimal('supplier_price', 10, 2)->nullable()->after('hex_code');
        });

        Schema::table('product_sizes', function (Blueprint $table) {
            $table->decimal('price_supplement', 8, 2)->default(0)->after('size');
        });
    }

    public function down(): void
    {
        Schema::table('product_colors', function (Blueprint $table) {
            $table->dropColumn('supplier_price');
        });

        Schema::table('product_sizes', function (Blueprint $table) {
            $table->dropColumn('price_supplement');
        });
    }
};
