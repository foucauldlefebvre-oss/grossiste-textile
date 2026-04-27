<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('brand_colors', function (Blueprint $table) {
            $table->id();
            $table->string('brand');        // Roly, Kariban, Stanley/Stella, etc.
            $table->string('name');         // Noir, Blanc, Rouge...
            $table->string('hex_code', 7)->nullable(); // #000000
            $table->string('color_code')->nullable();  // Supplier internal code
            $table->timestamps();

            $table->unique(['brand', 'name']);
            $table->index('brand');
        });

        Schema::create('brand_sizes', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('size');          // S, M, L, XL, 2XL...
            $table->timestamps();

            $table->unique(['brand', 'size']);
            $table->index('brand');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('brand_sizes');
        Schema::dropIfExists('brand_colors');
    }
};
