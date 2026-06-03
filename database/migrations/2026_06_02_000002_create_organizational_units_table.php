<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('organizational_units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('unit_type', 10)->comment('dpc|dpd|dpp');
            $table->char('province_code', 2)->nullable()->comment('FK wilindo_provinces.code; wajib untuk DPD dan DPC');
            $table->char('city_code', 4)->nullable()->comment('FK wilindo_cities.code; wajib untuk DPC saja');
            $table->string('name', 255)->comment('mis. DPC Kota Bandung');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('province_code');
            $table->index('city_code');
            $table->index(['unit_type', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('organizational_units');
    }
};
