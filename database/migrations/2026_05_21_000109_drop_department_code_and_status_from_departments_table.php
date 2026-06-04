<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('departments', 'department_code')) {
                $table->dropUnique(['department_code']);
                $columns[] = 'department_code';
            }

            if (Schema::hasColumn('departments', 'status')) {
                $columns[] = 'status';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            if (! Schema::hasColumn('departments', 'department_code')) {
                $table->string('department_code', 20)->unique()->after('id');
            }

            if (! Schema::hasColumn('departments', 'status')) {
                $table->string('status', 20)->default('active')->after('department_name');
            }
        });
    }
};
