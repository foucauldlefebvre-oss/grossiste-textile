<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_conversations', function (Blueprint $table) {
            $table->id();
            $table->string('session_token', 64)->unique(); // identifiant anonyme du visiteur
            $table->string('visitor_name', 100)->nullable();
            $table->string('visitor_email', 200)->nullable();
            $table->enum('status', ['bot', 'waiting', 'active', 'closed'])->default('bot');
            $table->boolean('is_read_admin')->default(false);
            $table->timestamp('last_message_at')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('last_message_at');
        });

        Schema::create('chat_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chat_conversation_id')->constrained()->cascadeOnDelete();
            $table->enum('sender', ['visitor', 'bot', 'admin'])->default('visitor');
            $table->text('body');
            $table->timestamps();

            $table->index('chat_conversation_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
        Schema::dropIfExists('chat_conversations');
    }
};
