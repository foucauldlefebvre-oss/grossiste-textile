<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'has_marking',
                'status_bat',
                'bat_status',
                'bat_client_comment',
                'bat_client_decided_at',
                'bat_token',
                'bat_done_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('has_marking')->default(false);
            $table->enum('status_bat', ['pending', 'sent', 'approved', 'revision_requested', 'none'])->default('pending');
            $table->enum('bat_status', ['none', 'pending', 'sent', 'approved', 'revision_requested'])->default('none');
            $table->text('bat_client_comment')->nullable();
            $table->timestamp('bat_client_decided_at')->nullable();
            $table->string('bat_token')->nullable();
            $table->timestamp('bat_done_at')->nullable();
        });
    }
};
