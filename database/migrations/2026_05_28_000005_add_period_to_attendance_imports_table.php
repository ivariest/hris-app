<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_imports', function (Blueprint $table) {
            $table->date('period_start')->nullable()->after('file_path');
            $table->date('period_end')->nullable()->after('period_start');
        });
    }

    public function down(): void
    {
        Schema::table('attendance_imports', function (Blueprint $table) {
            $table->dropColumn(['period_start', 'period_end']);
        });
    }
};
