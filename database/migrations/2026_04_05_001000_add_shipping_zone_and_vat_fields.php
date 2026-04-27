<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Users: add VAT intra number
        Schema::table('users', function (Blueprint $table) {
            $table->string('vat_number', 20)->nullable()->after('siret');
        });

        // Orders: add shipping zone and TVA exemption info
        Schema::table('orders', function (Blueprint $table) {
            $table->string('shipping_zone', 20)->default('france')->after('shipping_ht');
            $table->decimal('shipping_per_parcel', 10, 2)->default(9.50)->after('shipping_zone');
            $table->string('vat_exemption', 30)->nullable()->after('total_ttc');
            $table->string('customer_vat_number', 20)->nullable()->after('vat_exemption');
            $table->string('customer_siret', 20)->nullable()->after('customer_vat_number');
        });

        // Quotes: add shipping zone
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('shipping_zone', 20)->default('france')->after('shipping_parcels');
            $table->decimal('shipping_per_parcel', 10, 2)->default(9.50)->after('shipping_zone');
        });

        // Invoices: add exemption mention and customer SIRET
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('vat_exemption', 30)->nullable()->after('vat_number');
            $table->string('customer_siret', 20)->nullable()->after('vat_exemption');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('vat_number');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_zone', 'shipping_per_parcel', 'vat_exemption', 'customer_vat_number', 'customer_siret']);
        });

        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['shipping_zone', 'shipping_per_parcel']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['vat_exemption', 'customer_siret']);
        });
    }
};
