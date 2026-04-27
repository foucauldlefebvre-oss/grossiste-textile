<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_rates', function (Blueprint $table) {
            $table->id();
            $table->string('carrier');
            $table->string('zone');
            $table->decimal('weight_min', 8, 3)->default(0);
            $table->decimal('weight_max', 8, 3);
            $table->decimal('price_ht', 10, 2);
            $table->integer('delivery_days_min')->nullable();
            $table->integer('delivery_days_max')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['carrier', 'zone']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_rates');
    }
};
