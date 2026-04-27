<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Clean existing grammage string values before type conversion
        DB::statement("UPDATE products SET grammage = NULLIF(REGEXP_REPLACE(grammage, '[^0-9]', ''), '') WHERE grammage IS NOT NULL");

        Schema::table('products', function (Blueprint $table) {
            // Convert grammage from string to integer
            $table->integer('grammage')->nullable()->change();

            // New product fields
            $table->integer('min_order_quantity')->default(1)->after('stock');
            $table->integer('package_weight')->nullable()->after('weight');

            // Marking compatibility (JSON array of technique IDs)
            $table->json('compatible_techniques')->nullable()->after('certifications');

            // SEO fields
            $table->string('meta_title', 255)->nullable()->after('sort_order');
            $table->text('meta_description')->nullable()->after('meta_title');
            $table->string('seo_keywords', 500)->nullable()->after('meta_description');
            $table->string('seo_url', 255)->nullable()->unique()->after('seo_keywords');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('grammage')->nullable()->change();

            $table->dropColumn([
                'min_order_quantity',
                'package_weight',
                'compatible_techniques',
                'meta_title',
                'meta_description',
                'seo_keywords',
            ]);

            $table->dropUnique(['seo_url']);
            $table->dropColumn('seo_url');
        });
    }
};
