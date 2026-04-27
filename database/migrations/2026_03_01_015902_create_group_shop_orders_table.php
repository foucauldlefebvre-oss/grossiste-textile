<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_shop_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_shop_id')->constrained()->cascadeOnDelete();
            $table->foreignId('group_shop_product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('department')->nullable();
            $table->string('size');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->enum('payment_status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->string('stripe_payment_intent_id')->nullable();
            $table->timestamps();

            $table->index('group_shop_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_shop_orders');
    }
};
