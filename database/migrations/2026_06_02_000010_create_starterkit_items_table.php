<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('starterkit_items', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->comment('mis. Kaos Polo ASPERDA 2025');
            $table->text('description')->nullable();
            $table->char('period_year', 4);
            $table->unsignedInteger('stock_total')->default(0);
            $table->unsignedInteger('stock_distributed')->default(0)
                ->comment('Update via DB::transaction + lockForUpdate');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['period_year', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('starterkit_items');
    }
};
