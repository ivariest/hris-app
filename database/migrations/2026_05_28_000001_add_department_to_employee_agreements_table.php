<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_agreements', function (Blueprint $table) {
            $table->string('department', 150)->nullable()->after('employee_name');
        });

        DB::table('employee_agreements')
            ->whereNotNull('recruitment_request_id')
            ->orderBy('id')
            ->eachById(function ($agreement) {
                $department = DB::table('recruitment_requests')
                    ->where('id', $agreement->recruitment_request_id)
                    ->value('requester_department');

                DB::table('employee_agreements')
                    ->where('id', $agreement->id)
                    ->update(['department' => $department]);
            });
    }

    public function down(): void
    {
        Schema::table('employee_agreements', function (Blueprint $table) {
            $table->dropColumn('department');
        });
    }
};
