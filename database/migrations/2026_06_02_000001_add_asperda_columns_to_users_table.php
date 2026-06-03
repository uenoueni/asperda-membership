<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // password nullable: null saat menunggu konfirmasi email
            $table->string('password')->nullable()->change();

            $table->string('role', 20)->default('member')->after('email')
                ->comment('super_admin|dpp|dpd|dpc|member');
            $table->string('phone', 20)->nullable()->after('role');
            $table->string('bank_name', 100)->nullable()->after('phone');
            $table->string('bank_account_no', 50)->nullable()->after('bank_name');
            $table->string('bank_account_name', 255)->nullable()->after('bank_account_no');

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropColumn(['role', 'phone', 'bank_name', 'bank_account_no', 'bank_account_name']);
            $table->string('password')->nullable(false)->change();
        });
    }
};
