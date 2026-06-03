<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refunds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('survey_assignment_id')->constrained('survey_assignments');
            $table->text('rejection_reason')->comment('Salinan dari survey_assignment.rejection_reason');
            $table->foreignId('rejected_by')->constrained('users');
            $table->decimal('original_amount', 15, 2)->comment('Nominal payment awal');
            $table->decimal('refund_amount', 15, 2)->comment('Bisa berbeda jika ada potongan admin');
            // Snapshot rekening saat record dibuat — tidak berubah meski user update profil
            $table->string('bank_name', 100);
            $table->string('bank_account_no', 50);
            $table->string('bank_account_name', 255);
            $table->date('planned_refund_date')->nullable();
            $table->date('actual_refund_date')->nullable();
            $table->string('status', 20)->default('queued')
                ->comment('queued|processing|completed|cancelled');
            $table->foreignId('processed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refunds');
    }
};
