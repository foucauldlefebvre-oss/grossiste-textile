<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('techniques_marquage', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('description_courte');
            $table->string('image')->nullable();
            $table->integer('ordre')->default(0);
            $table->boolean('actif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('techniques_marquage');
    }
};
