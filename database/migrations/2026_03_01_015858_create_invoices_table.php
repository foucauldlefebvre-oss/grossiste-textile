<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->enum('type', ['invoice', 'credit_note'])->default('invoice');
            $table->decimal('total_ht', 12, 2);
            $table->decimal('total_tva', 12, 2);
            $table->decimal('total_ttc', 12, 2);
            $table->string('vat_number')->nullable();
            $table->json('billing_address')->nullable();
            $table->string('pdf_path')->nullable();
            $table->timestamp('issued_at');
            $table->timestamps();

            $table->index('order_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
