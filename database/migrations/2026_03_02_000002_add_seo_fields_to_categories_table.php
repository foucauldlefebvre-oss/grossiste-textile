<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->text('meta_description')->nullable()->after('description');
            $table->string('seo_keywords', 500)->nullable()->after('meta_description');
            $table->json('default_techniques')->nullable()->after('seo_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['meta_description', 'seo_keywords', 'default_techniques']);
        });
    }
};
