<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_color_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_size_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('marking_technique_id')->nullable()->constrained()->nullOnDelete();
            $table->integer('quantity');
            $table->decimal('unit_price_ht', 10, 2);
            $table->decimal('marking_price_ht', 10, 2)->default(0);
            $table->decimal('line_total_ht', 10, 2);
            $table->string('visual_file')->nullable();
            $table->string('marking_zone')->nullable();
            $table->integer('visual_colors')->nullable();
            $table->string('bat_pdf')->nullable();
            $table->enum('bat_status', ['pending', 'sent', 'approved', 'rejected'])->nullable();
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
