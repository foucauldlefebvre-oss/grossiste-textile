<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('technique_constraints', function (Blueprint $table) {
            $table->string('max_format', 10)->nullable()->after('max_colors')->comment('A7, A6, A5, A4, A3');
        });
    }

    public function down(): void
    {
        Schema::table('technique_constraints', function (Blueprint $table) {
            $table->dropColumn('max_format');
        });
    }
};
