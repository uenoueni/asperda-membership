<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starterkit_distributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('starterkit_items');
            $table->char('period_year', 4);
            $table->string('status', 20)->default('pending')
                ->comment('pending|distributed|confirmed');
            $table->dateTime('distributed_at')->nullable();
            $table->dateTime('confirmed_at')->nullable();
            $table->foreignId('distributed_by')->nullable()->constrained('users');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['member_id', 'period_year']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starterkit_distributions');
    }
};
