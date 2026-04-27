<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('reference')->unique();
            $table->string('supplier')->nullable();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('material')->nullable();
            $table->string('grammage')->nullable();
            $table->enum('cut', ['homme', 'femme', 'mixte', 'enfant'])->default('mixte');
            $table->json('certifications')->nullable();
            $table->decimal('base_price', 10, 2)->nullable();
            $table->decimal('weight', 8, 3)->nullable();
            $table->string('main_image')->nullable();
            $table->json('gallery')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->integer('stock')->default(0);
            $table->timestamps();

            $table->index('category_id');
            $table->index('supplier');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
