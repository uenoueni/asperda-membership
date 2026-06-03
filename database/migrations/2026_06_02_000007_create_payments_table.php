<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('midtrans_order_id', 100)->unique()
                ->comment('Format: ASPERDA-{member_id}-{timestamp_unix}');
            $table->string('midtrans_transaction_id', 100)->nullable()
                ->comment('Diisi setelah notifikasi Midtrans');
            $table->string('type', 20)->comment('registration|renewal');
            $table->char('period_year', 4);
            $table->decimal('amount', 15, 2);
            $table->string('status', 20)->default('pending')
                ->comment('pending|paid|failed|expired|refunded');
            $table->dateTime('paid_at')->nullable();
            $table->string('payment_channel', 50)->nullable()->comment('mis. bca_va|gopay|qris');
            $table->json('midtrans_raw_response')->nullable()->comment('Raw callback Midtrans untuk audit');
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index('status');
            $table->index('midtrans_transaction_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
