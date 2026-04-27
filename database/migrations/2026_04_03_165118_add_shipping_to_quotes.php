<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->decimal('shipping_ht', 10, 2)->default(0)->after('total_ht');
            $table->integer('shipping_parcels')->default(0)->after('shipping_ht');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['shipping_ht', 'shipping_parcels']);
        });
    }
};
