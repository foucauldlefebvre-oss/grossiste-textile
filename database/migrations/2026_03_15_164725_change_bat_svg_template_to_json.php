<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->text('bat_svg_template')->nullable()->change();
        });

        // Convert existing string values to JSON object {"0": "value"}
        DB::statement("
            UPDATE quotes
            SET bat_svg_template = JSON_OBJECT('0', bat_svg_template)
            WHERE bat_svg_template IS NOT NULL
            AND bat_svg_template NOT LIKE '{%}'
        ");
    }

    public function down(): void
    {
        // Extract value from JSON back to string
        DB::statement("
            UPDATE quotes
            SET bat_svg_template = JSON_UNQUOTE(JSON_EXTRACT(bat_svg_template, '$.\"0\"'))
            WHERE bat_svg_template IS NOT NULL
            AND bat_svg_template LIKE '{%}'
        ");

        Schema::table('quotes', function (Blueprint $table) {
            $table->string('bat_svg_template', 50)->nullable()->change();
        });
    }
};
