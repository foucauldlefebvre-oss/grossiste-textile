<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('status', 20)->default('active'); // active | converted | abandoned
            $table->decimal('subtotal_ht', 10, 2)->default(0);
            $table->decimal('shipping_ht', 10, 2)->default(0);
            $table->integer('shipping_parcels')->default(0);
            $table->string('shipping_zone', 20)->default('france'); // france | eu | suisse | dom_tom
            $table->decimal('shipping_per_parcel', 10, 2)->default(0);
            $table->decimal('total_ht', 10, 2)->default(0);
            $table->decimal('total_tva', 10, 2)->default(0);
            $table->decimal('total_ttc', 10, 2)->default(0);
            $table->string('vat_exemption', 20)->nullable(); // intra_eu | export | dom_tom | null
            $table->timestamp('converted_at')->nullable();
            $table->timestamp('abandoned_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'status']);
            $table->index(['status', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
