<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->string('label');
            $table->decimal('min_price', 10, 2);
            $table->decimal('max_price', 10, 2)->nullable();
            $table->unsignedInteger('min_quantity');
            $table->unsignedInteger('max_quantity')->nullable();
            $table->decimal('coefficient', 6, 3)->default(1.000);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['min_price', 'max_price', 'min_quantity']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pricing_rules');
    }
};
