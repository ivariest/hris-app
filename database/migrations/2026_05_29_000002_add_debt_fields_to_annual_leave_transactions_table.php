<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('annual_leave_transactions', function (Blueprint $table) {
            if (! Schema::hasColumn('annual_leave_transactions', 'balance_effect')) {
                $table->string('balance_effect', 30)->default('annual_leave')->after('source_type');
            }

            if (! Schema::hasColumn('annual_leave_transactions', 'written_off_at')) {
                $table->timestamp('written_off_at')->nullable()->after('balance_effect');
            }
        });
    }

    public function down(): void
    {
        Schema::table('annual_leave_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('annual_leave_transactions', 'written_off_at')) {
                $table->dropColumn('written_off_at');
            }

            if (Schema::hasColumn('annual_leave_transactions', 'balance_effect')) {
                $table->dropColumn('balance_effect');
            }
        });
    }
};
