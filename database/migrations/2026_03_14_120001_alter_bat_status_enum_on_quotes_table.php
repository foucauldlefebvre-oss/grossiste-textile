<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE quotes MODIFY COLUMN bat_status ENUM('pending','sent','approved','rejected','revision_requested') NOT NULL DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE quotes MODIFY COLUMN bat_status ENUM('pending','sent','approved','rejected') NOT NULL DEFAULT 'pending'");
    }
};
