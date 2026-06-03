<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_id')->constrained()->cascadeOnDelete();
            $table->string('cert_number', 100)->unique()
                ->comment('Format: ASPERDA/{period_year}/{member_no}/{seq}');
            $table->char('period_year', 4);
            $table->date('valid_from');
            $table->date('valid_until');
            $table->string('file_path', 500)->nullable()->comment('Path PDF di storage');
            $table->dateTime('generated_at')->nullable()->comment('Null jika belum di-generate');
            $table->timestamps();

            $table->index(['member_id', 'period_year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};
