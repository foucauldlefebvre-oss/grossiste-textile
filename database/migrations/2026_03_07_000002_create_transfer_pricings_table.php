<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transfer_pricings', function (Blueprint $table) {
            $table->id();
            $table->string('technique', 30);
            $table->string('format', 5);
            $table->unsignedInteger('quantity');
            $table->decimal('unit_price', 8, 2);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['technique', 'format', 'quantity'], 'transfer_tech_fmt_qty');
        });

        // Allow decimal quality (4.5/5)
        DB::statement('ALTER TABLE marking_techniques MODIFY quality_rating DECIMAL(2,1) NULL');
    }

    public function down(): void
    {
        Schema::dropIfExists('transfer_pricings');

        if (Schema::hasColumn('marking_techniques', 'quality_rating')) {
            DB::statement('ALTER TABLE marking_techniques MODIFY quality_rating TINYINT UNSIGNED NULL');
        }
    }
};
