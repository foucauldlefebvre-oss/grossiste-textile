<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type');
            $table->enum('channel', ['email', 'sms']);
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('content')->nullable();
            $table->enum('status', ['sent', 'failed', 'pending'])->default('pending');
            $table->text('error')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
    }
};
