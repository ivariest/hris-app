<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->foreignId('level_id')
                ->nullable()
                ->after('position_id')
                ->constrained('position_levels')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('employee_positions', function (Blueprint $table) {
            $table->dropForeign(['level_id']);
            $table->dropColumn('level_id');
        });
    }
};
