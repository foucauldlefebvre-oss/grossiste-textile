<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('has_marking')->default(false)->after('status');
            $table->enum('bat_status', ['none', 'pending', 'sent', 'approved', 'revision_requested'])->default('none')->after('status_bat');
            $table->text('bat_client_comment')->nullable()->after('bat_status');
            $table->timestamp('bat_client_decided_at')->nullable()->after('bat_client_comment');
            $table->string('bat_token', 64)->nullable()->unique()->after('bat_client_decided_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['has_marking', 'bat_status', 'bat_client_comment', 'bat_client_decided_at', 'bat_token']);
        });
    }
};
