<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sanctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_assignment_id')->constrained('survey_assignments');
            $table->foreignId('issued_to')->constrained('users');
            $table->foreignId('issued_by')->constrained('users');
            $table->text('reason');
            $table->string('severity', 20)->comment('warning|suspension|termination');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('issued_to');
            $table->index('survey_assignment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sanctions');
    }
};
