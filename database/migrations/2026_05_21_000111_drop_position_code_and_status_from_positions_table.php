<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $columns = [];

            if (Schema::hasColumn('positions', 'position_code')) {
                $table->dropUnique(['position_code']);
                $columns[] = 'position_code';
            }

            if (Schema::hasColumn('positions', 'status')) {
                $columns[] = 'status';
            }

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            if (! Schema::hasColumn('positions', 'position_code')) {
                $table->string('position_code', 20)->unique()->after('id');
            }

            if (! Schema::hasColumn('positions', 'status')) {
                $table->string('status', 20)->default('active')->after('position_name');
            }
        });
    }
};
