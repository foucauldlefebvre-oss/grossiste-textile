<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('bat_pdf')->nullable()->after('notes');
            $table->enum('bat_status', ['pending', 'sent', 'approved', 'rejected'])->default('pending')->after('bat_pdf');
            $table->timestamp('bat_sent_at')->nullable()->after('bat_status');
            $table->string('bat_token', 64)->nullable()->unique()->after('bat_sent_at');
            $table->text('bat_rejection_reason')->nullable()->after('bat_token');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn(['bat_pdf', 'bat_status', 'bat_sent_at', 'bat_token', 'bat_rejection_reason']);
        });
    }
};
