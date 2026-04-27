<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->string('bat_logo_path', 255)->nullable()->after('bat_rejection_reason');
            $table->decimal('bat_logo_x', 5, 2)->default(0)->after('bat_logo_path');
            $table->decimal('bat_logo_y', 5, 2)->default(0)->after('bat_logo_x');
            $table->decimal('bat_logo_width', 5, 2)->nullable()->after('bat_logo_y');
            $table->decimal('bat_logo_height', 5, 2)->nullable()->after('bat_logo_width');
            $table->string('bat_format', 10)->nullable()->after('bat_logo_height');
            $table->string('bat_position_label', 100)->nullable()->after('bat_format');
            $table->string('bat_svg_template', 50)->nullable()->after('bat_position_label');
            $table->text('bat_client_comment')->nullable()->after('bat_svg_template');
            $table->timestamp('bat_decided_at')->nullable()->after('bat_client_comment');
        });
    }

    public function down(): void
    {
        Schema::table('quotes', function (Blueprint $table) {
            $table->dropColumn([
                'bat_logo_path',
                'bat_logo_x',
                'bat_logo_y',
                'bat_logo_width',
                'bat_logo_height',
                'bat_format',
                'bat_position_label',
                'bat_svg_template',
                'bat_client_comment',
                'bat_decided_at',
            ]);
        });
    }
};
