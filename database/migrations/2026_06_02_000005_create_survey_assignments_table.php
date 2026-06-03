<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('survey_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->foreignId('assigned_to')->constrained('users');
            $table->foreignId('organizational_unit_id')->constrained('organizational_units');
            $table->string('level', 10)->comment('dpc|dpd|dpp');
            $table->string('status', 20)->default('pending')
                ->comment('pending|accepted|approved|rejected|escalated');
            $table->text('rejection_reason')->nullable()->comment('Wajib diisi jika status rejected');
            $table->dateTime('deadline');
            $table->dateTime('accepted_at')->nullable();
            $table->dateTime('decided_at')->nullable();
            $table->unsignedBigInteger('escalated_from')->nullable()
                ->comment('Self-referencing FK ke survey_assignments.id');
            $table->foreign('escalated_from')->references('id')->on('survey_assignments');
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['assigned_to', 'status']);
            $table->index('deadline');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('survey_assignments');
    }
};
