<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_shifts', function (Blueprint $table) {
            $table->id();
            $table->string('shift_name', 100);
            $table->time('check_in_time');
            $table->time('check_out_time');
            $table->unsignedSmallInteger('late_tolerance_minutes')->default(0);
            $table->unsignedSmallInteger('early_leave_tolerance_minutes')->default(0);
            $table->boolean('is_default')->default(false);
            $table->string('status', 20)->default('active');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('employee_attendance_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('attendance_shift_id')->constrained('attendance_shifts')->cascadeOnDelete();
            $table->date('effective_date')->nullable();
            $table->timestamps();

            $table->unique('employee_id');
        });

        Schema::create('attendance_holidays', function (Blueprint $table) {
            $table->id();
            $table->date('holiday_date')->unique();
            $table->string('holiday_name', 150);
            $table->string('holiday_type', 30)->default('national_holiday');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('attendance_imports', function (Blueprint $table) {
            $table->id();
            $table->string('file_name');
            $table->string('file_path');
            $table->foreignId('imported_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedInteger('total_rows')->default(0);
            $table->unsignedInteger('matched_rows')->default(0);
            $table->unsignedInteger('unmatched_rows')->default(0);
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();
        });

        Schema::create('attendance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attendance_import_id')->constrained('attendance_imports')->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->string('attendance_id', 50);
            $table->string('fingerprint_name', 150)->nullable();
            $table->date('scan_date')->nullable();
            $table->time('check_in')->nullable();
            $table->time('check_out')->nullable();
            $table->string('status', 30)->default('unmatched');
            $table->unsignedInteger('late_minutes')->default(0);
            $table->unsignedInteger('early_leave_minutes')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['attendance_id', 'scan_date']);
            $table->index(['employee_id', 'scan_date']);
        });

        DB::table('attendance_shifts')->insert([
            'shift_name' => 'SHIFT NORMAL',
            'check_in_time' => '08:00:00',
            'check_out_time' => '17:00:00',
            'late_tolerance_minutes' => 0,
            'early_leave_tolerance_minutes' => 0,
            'is_default' => true,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_logs');
        Schema::dropIfExists('attendance_imports');
        Schema::dropIfExists('attendance_holidays');
        Schema::dropIfExists('employee_attendance_shifts');
        Schema::dropIfExists('attendance_shifts');
    }
};
