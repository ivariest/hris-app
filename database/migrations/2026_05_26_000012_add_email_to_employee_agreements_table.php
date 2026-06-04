<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('employee_agreements', 'email')) {
            return;
        }

        Schema::table('employee_agreements', function (Blueprint $table) {
            $table->string('email', 100)->nullable()->after('phone_number');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('employee_agreements', 'email')) {
            return;
        }

        Schema::table('employee_agreements', function (Blueprint $table) {
            $table->dropColumn('email');
        });
    }
};
