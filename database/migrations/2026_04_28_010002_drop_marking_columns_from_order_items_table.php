<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Note: la FK order_items_marking_technique_id_foreign avait déjà été droppée
        // lors d'une tentative antérieure de cette migration (qui a échoué sur marking_group
        // qui n'existait pas dans le schéma). On la re-drop conditionnellement pour
        // garantir l'idempotence si on rollback puis relance.
        $fkExists = collect(\Illuminate\Support\Facades\DB::select("
            SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'order_items'
              AND CONSTRAINT_NAME = 'order_items_marking_technique_id_foreign'
        "))->isNotEmpty();

        Schema::table('order_items', function (Blueprint $table) use ($fkExists) {
            if ($fkExists) {
                $table->dropForeign(['marking_technique_id']);
            }
            $table->dropColumn([
                'marking_technique_id',
                'marking_price_ht',
                'visual_file',
                'marking_zone',
                'visual_colors',
                'bat_pdf',
                'bat_status',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->foreignId('marking_technique_id')->nullable()->constrained('marking_techniques')->nullOnDelete();
            $table->decimal('marking_price_ht', 10, 2)->default(0);
            $table->string('visual_file')->nullable();
            $table->string('marking_zone')->nullable();
            $table->integer('visual_colors')->nullable();
            $table->string('bat_pdf')->nullable();
            $table->enum('bat_status', ['pending', 'sent', 'approved', 'rejected'])->nullable();
        });
    }
};
