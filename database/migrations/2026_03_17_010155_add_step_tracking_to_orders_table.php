<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('amount_paid', 12, 2)->default(0)->after('total_ttc');

            $table->enum('status_bat', ['pending', 'done'])->default('pending')->after('payment_status');
            $table->timestamp('bat_done_at')->nullable()->after('status_bat');

            $table->enum('status_prep', ['pending', 'done'])->default('pending')->after('bat_done_at');
            $table->timestamp('prep_done_at')->nullable()->after('status_prep');

            $table->enum('status_production', ['pending', 'done'])->default('pending')->after('prep_done_at');
            $table->timestamp('production_done_at')->nullable()->after('status_production');

            $table->enum('status_shipping', ['pending', 'done'])->default('pending')->after('production_done_at');
            $table->timestamp('shipping_done_at')->nullable()->after('status_shipping');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'amount_paid',
                'status_bat', 'bat_done_at',
                'status_prep', 'prep_done_at',
                'status_production', 'production_done_at',
                'status_shipping', 'shipping_done_at',
            ]);
        });
    }
};
