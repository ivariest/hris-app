<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recruitment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number', 50)->unique();
            $table->foreignId('requester_employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('requester_department', 150)->nullable();
            $table->string('requester_position', 150)->nullable();
            $table->string('requester_level', 100)->nullable();
            $table->foreignId('company_id')->nullable()->constrained()->nullOnDelete();
            $table->date('request_date')->nullable();
            $table->foreignId('position_id')->nullable()->constrained()->nullOnDelete();
            $table->string('requested_position', 150);
            $table->unsignedInteger('needed_count')->default(1);
            $table->string('gender', 30)->nullable();
            $table->string('age', 50)->nullable();
            $table->string('request_status', 50)->nullable();
            $table->string('request_reason', 50)->nullable();
            $table->string('replacement_reason', 50)->nullable();
            $table->text('replacement_note')->nullable();
            $table->string('employee_status_agreement', 50)->nullable();
            $table->unsignedInteger('employment_duration_months')->nullable();
            $table->date('needed_date')->nullable();
            $table->string('posting_media', 50)->nullable();
            $table->string('minimum_education', 100)->nullable();
            $table->string('major', 150)->nullable();
            $table->text('special_requirements')->nullable();
            $table->text('general_requirements')->nullable();
            $table->text('job_summary')->nullable();
            $table->text('requirement_detail')->nullable();
            $table->string('status', 20)->default('on_going');
            $table->text('pending_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recruitment_requests');
    }
};
