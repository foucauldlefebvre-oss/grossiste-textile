<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marking_techniques', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('description');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->text('seo_content')->nullable()->after('meta_description');
            $table->string('seo_url')->nullable()->unique()->after('seo_content');
        });
    }

    public function down(): void
    {
        Schema::table('marking_techniques', function (Blueprint $table) {
            $table->dropColumn(['meta_title', 'meta_description', 'seo_content', 'seo_url']);
        });
    }
};
