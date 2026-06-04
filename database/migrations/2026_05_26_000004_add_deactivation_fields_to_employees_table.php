<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('deactivation_reason', 30)->nullable()->after('status_karyawan');
            $table->timestamp('deactivated_at')->nullable()->after('deactivation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['deactivation_reason', 'deactivated_at']);
        });
    }
};
