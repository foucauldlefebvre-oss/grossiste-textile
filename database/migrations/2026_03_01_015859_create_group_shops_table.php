<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('group_shops', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('password');
            $table->text('description')->nullable();
            $table->string('logo')->nullable();
            $table->enum('pricing_mode', ['fixed', 'degressive'])->default('fixed');
            $table->enum('payment_mode', ['individual', 'collective'])->default('individual');
            $table->enum('status', ['draft', 'open', 'closed', 'archived'])->default('draft');
            $table->timestamp('opens_at')->nullable();
            $table->timestamp('closes_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('group_shops');
    }
};
