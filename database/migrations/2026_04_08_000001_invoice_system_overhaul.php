<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status', 20)->default('paid')->after('type'); // deposit, paid, settled
            $table->string('payment_method', 20)->nullable()->after('status'); // cb, wire_transfer
            $table->timestamp('payment_due_at')->nullable()->after('issued_at');
            $table->date('delivery_date')->nullable()->after('payment_due_at');
            $table->decimal('amount_paid', 12, 2)->default(0)->after('total_ttc');
            $table->decimal('amount_remaining', 12, 2)->default(0)->after('amount_paid');
        });

        // Backfill existing invoices
        $invoices = DB::table('invoices')
            ->join('orders', 'orders.id', '=', 'invoices.order_id')
            ->select('invoices.id', 'orders.payment_status', 'orders.amount_paid', 'orders.total_ttc', 'orders.stripe_payment_intent_id')
            ->get();

        foreach ($invoices as $inv) {
            $status = match ($inv->payment_status) {
                'partial' => 'deposit',
                'paid' => 'paid',
                default => 'paid',
            };
            $method = $inv->stripe_payment_intent_id ? 'cb' : 'wire_transfer';
            $amountPaid = (float) ($inv->amount_paid ?? 0);
            $remaining = max(0, (float) $inv->total_ttc - $amountPaid);

            DB::table('invoices')->where('id', $inv->id)->update([
                'status' => $status,
                'payment_method' => $method,
                'amount_paid' => $amountPaid,
                'amount_remaining' => $remaining,
            ]);
        }
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_method', 'payment_due_at', 'delivery_date', 'amount_paid', 'amount_remaining']);
        });
    }
};
