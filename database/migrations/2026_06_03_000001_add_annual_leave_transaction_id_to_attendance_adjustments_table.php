<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_adjustments', function (Blueprint $table) {
            if (! Schema::hasColumn('attendance_adjustments', 'annual_leave_transaction_id')) {
                $table->foreignId('annual_leave_transaction_id')
                    ->nullable()
                    ->after('updated_by')
                    ->constrained('annual_leave_transactions')
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('attendance_adjustments', function (Blueprint $table) {
            if (Schema::hasColumn('attendance_adjustments', 'annual_leave_transaction_id')) {
                $table->dropConstrainedForeignId('annual_leave_transaction_id');
            }
        });
    }
};
