<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_sizes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_color_id')->constrained()->cascadeOnDelete();
            $table->string('size');
            $table->integer('stock')->default(0);
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->unique(['product_color_id', 'size']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_sizes');
    }
};
