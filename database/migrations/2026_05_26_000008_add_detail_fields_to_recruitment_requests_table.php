<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('recruitment_requests', 'requester_employee_id')) {
            return;
        }

        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->foreignId('requester_employee_id')->nullable()->after('request_number')->constrained('employees')->nullOnDelete();
            $table->string('requester_department', 150)->nullable()->after('requester_employee_id');
            $table->string('requester_position', 150)->nullable()->after('requester_department');
            $table->string('requester_level', 100)->nullable()->after('requester_position');
            $table->foreignId('company_id')->nullable()->after('requester_level')->constrained()->nullOnDelete();
            $table->date('request_date')->nullable()->after('company_id');
            $table->foreignId('position_id')->nullable()->after('request_date')->constrained()->nullOnDelete();
            $table->string('gender', 30)->nullable()->after('needed_count');
            $table->string('age', 50)->nullable()->after('gender');
            $table->string('request_status', 50)->nullable()->after('age');
            $table->string('request_reason', 50)->nullable()->after('request_status');
            $table->string('replacement_reason', 50)->nullable()->after('request_reason');
            $table->text('replacement_note')->nullable()->after('replacement_reason');
            $table->string('employee_status_agreement', 50)->nullable()->after('replacement_note');
            $table->unsignedInteger('employment_duration_months')->nullable()->after('employee_status_agreement');
            $table->string('posting_media', 50)->nullable()->after('needed_date');
            $table->string('minimum_education', 100)->nullable()->after('posting_media');
            $table->string('major', 150)->nullable()->after('minimum_education');
            $table->text('special_requirements')->nullable()->after('major');
            $table->text('general_requirements')->nullable()->after('special_requirements');
            $table->text('job_summary')->nullable()->after('general_requirements');
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('recruitment_requests', 'requester_employee_id')) {
            return;
        }

        Schema::table('recruitment_requests', function (Blueprint $table) {
            $table->dropConstrainedForeignId('requester_employee_id');
            $table->dropConstrainedForeignId('company_id');
            $table->dropConstrainedForeignId('position_id');
            $table->dropColumn([
                'requester_department',
                'requester_position',
                'requester_level',
                'request_date',
                'gender',
                'age',
                'request_status',
                'request_reason',
                'replacement_reason',
                'replacement_note',
                'employee_status_agreement',
                'employment_duration_months',
                'posting_media',
                'minimum_education',
                'major',
                'special_requirements',
                'general_requirements',
                'job_summary',
            ]);
        });
    }
};
