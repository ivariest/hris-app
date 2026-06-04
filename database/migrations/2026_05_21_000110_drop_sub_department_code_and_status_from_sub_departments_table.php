<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sub_departments', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('sub_departments', 'sub_department_code')) {
                $table->dropUnique(['sub_department_code']);
                $columns[] = 'sub_department_code';
            }

            if (Schema::hasColumn('sub_departments', 'status')) {
                $columns[] = 'status';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('sub_departments', function (Blueprint $table) {
            if (! Schema::hasColumn('sub_departments', 'sub_department_code')) {
                $table->string('sub_department_code', 20)->unique()->after('id');
            }

            if (! Schema::hasColumn('sub_departments', 'status')) {
                $table->string('status', 20)->default('active')->after('sub_department_name');
            }
        });
    }
};
