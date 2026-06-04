<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recruitment_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('recruitment_requests', 'pending_reason')) {
                $table->text('pending_reason')->nullable()->after('status');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recruitment_requests', function (Blueprint $table) {
            if (Schema::hasColumn('recruitment_requests', 'pending_reason')) {
                $table->dropColumn('pending_reason');
            }
        });
    }
};
