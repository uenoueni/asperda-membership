<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_id')->constrained()->cascadeOnDelete();
            $table->string('branch_name', 255)->comment('mis. Kantor Pusat Bandung');
            $table->char('province_code', 2)->comment('FK wilindo_provinces.code');
            $table->char('city_code', 4)->comment('FK wilindo_cities.code');
            $table->char('district_code', 7)->nullable()->comment('FK wilindo_districts.code');
            $table->text('address');
            $table->unsignedInteger('unit_count')->default(0)->comment('Jumlah armada unit kendaraan');
            $table->boolean('is_primary')->default(false)->comment('Hanya satu per member');
            $table->timestamps();

            $table->index('member_id');
            $table->index('city_code');
            $table->index('province_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
