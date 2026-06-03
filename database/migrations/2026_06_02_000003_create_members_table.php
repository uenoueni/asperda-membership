<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('membership_no', 50)->unique()->nullable()
                ->comment('Generate saat status menjadi active');
            $table->string('rental_name', 255);
            $table->string('status', 30)->default('pending_verification')
                ->comment('pending_verification|waiting_survey|active|rejected|expired');
            $table->date('registered_at')->nullable()->comment('Tanggal payment pertama lunas');
            $table->date('expires_at')->nullable();
            $table->char('period_year', 4)->comment('mis. 2025');
            $table->timestamps();

            $table->index(['status', 'period_year']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('members');
    }
};
