<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->string('path', 500);
            $table->string('ip_hash', 64); // SHA256 de l'IP (anonymise)
            $table->string('user_agent', 500)->nullable();
            $table->string('referer', 500)->nullable();
            $table->boolean('is_bot')->default(false);
            $table->timestamp('visited_at')->useCurrent();

            $table->index('visited_at');
            $table->index(['visited_at', 'is_bot']);
            $table->index('path');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};
